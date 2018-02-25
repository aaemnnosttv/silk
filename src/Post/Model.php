<?php

namespace Silk\Post;

use stdClass;
use WP_Post;
use Silk\Type\Model as BaseModel;
use Silk\PostType\PostType;
use Silk\Exception\WP_ErrorException;
use Silk\Post\Exception\PostNotFoundException;
use Silk\Post\Exception\ModelPostTypeMismatchException;

/**
 * @property-read WP_Post $post
 * @property-read int     $id
 *
 * @property int    $ID
 * @property int    $comment_count
 * @property string $comment_status
 * @property string $filter
 * @property string $guid
 * @property int    $menu_order
 * @property string $ping_status
 * @property string $pinged
 * @property int    $post_author
 * @property string $post_content
 * @property string $post_content_filtered
 * @property string $post_date
 * @property string $post_date_gmt
 * @property string $post_excerpt
 * @property string $post_mime_type
 * @property string $post_modified
 * @property string $post_modified_gmt
 * @property string $post_name
 * @property int    $post_parent
 * @property string $post_password
 * @property string $post_status
 * @property string $post_title
 * @property string $post_type
 * @property string $to_ping
 */
abstract class Model extends BaseModel
{
    /**
     * The post type of the post this model wraps
     * @var string
     */
    const POST_TYPE = '';

    /**
     * The object type in WordPress
     * @var string
     */
    const OBJECT_TYPE = 'post';

    /**
     * The primary ID property on the object
     */
    const ID_PROPERTY = 'ID';

    /**
     * Create a new instance
     *
     * @param array|WP_Post $post  Post object or array of attributes
     *
     * @throws ModelPostTypeMismatchException
     */
    public function __construct($post = [])
    {
        $attributes = is_array($post) ? $post : [];

        if (! $post instanceof WP_Post) {
            $post = new WP_Post(new stdClass);
            $post->post_type = static::postTypeId();
        } elseif ($post->post_type !== static::postTypeId()) {
            throw new ModelPostTypeMismatchException(static::class, $post);
        }

        $this->setObject($post);

        $this->fill($attributes);
    }

    /**
     * Retrieve a new instance by the ID.
     *
     * @param int|string $id Primary ID
     *
     * @return null|static
     */
    public static function find($id)
    {
        try {
            return static::fromID($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a new instance from a Post with the given ID
     *
     * @param  int|string $id  Post ID of post to create the instance from
     *
     * @return static
     */
    public static function fromID($id)
    {
        $post = WP_Post::get_instance($id);

        if (false === $post) {
            throw new PostNotFoundException("No post found with ID {$id}");
        }

        return new static($post);
    }

    /**
     * Create a new instance from a Post with the given slug
     *
     * @param  string $slug  the post slug
     *
     * @return static
     */
    public static function fromSlug($slug)
    {
        $found = static::whereSlug($slug)->limit(1)->results();

        if ($found->isEmpty()) {
            throw new PostNotFoundException("No post found with slug {$slug}");
        }

        return $found->first();
    }

    /**
     * Create a new instance from the global $post
     *
     * @return static
     */
    public static function fromGlobal()
    {
        if (! $GLOBALS['post'] instanceof WP_Post) {
            throw new PostNotFoundException('Global $post not an instance of WP_Post');
        }

        return new static($GLOBALS['post']);
    }

    /**
     * Get the post type identifier for this model
     *
     * @return string post type identifier (slug)
     */
    public static function postTypeId()
    {
        return static::POST_TYPE;
    }

    /**
     * Get the post type API
     *
     * @return mixed        Loads an existing type as a new PostType,
     *                      or returns a new PostTypeBuilder for registering a new type.
     */
    public static function postType()
    {
        return PostType::make(static::postTypeId());
    }

    /**
     * Get the permalink URL.
     *
     * @return string|bool  The permalink URL, or false if the post does not exist.
     */
    public function url()
    {
        return get_permalink($this->id);
    }

    /**
     * Send the post to the trash
     *
     * If trash is disabled, the post or page is permanently deleted.
     *
     * @return $this
     */
    public function trash()
    {
        if (wp_trash_post($this->id)) {
            $this->refresh();
        }

        return $this;
    }

    /**
     * Restore a post or page from the Trash
     *
     * @return $this
     */
    public function untrash()
    {
        if (wp_untrash_post($this->id)) {
            $this->refresh();
        }

        return $this;
    }

    /**
     * Get a new query builder for the model.
     *
     * @return QueryBuilder
     */
    public function newQuery()
    {
        return QueryBuilder::make()->setModel($this);
    }

    /**
     * Save the post to the database.
     *
     * @throws WP_ErrorException
     *
     * @return $this
     */
    public function save()
    {
        if (! $this->id) {
            $result = wp_insert_post($this->object->to_array(), true);
        } else {
            $result = wp_update_post($this->object, true);
        }

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        $this->setId($result)->refresh();

        return $this;
    }

    /**
     * Permanently delete the post from the database.
     *
     * @return $this
     */
    public function delete()
    {
        if (wp_delete_post($this->id, true)) {
            $this->refresh();
        }

        return $this;
    }

    /**
     * Update the modeled object with the current state from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->setObject(WP_Post::get_instance($this->id));

        return $this;
    }
}
