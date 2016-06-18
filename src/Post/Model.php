<?php

namespace Silk\Post;

use stdClass;
use WP_Post;
use WP_Query;
use Silk\Query\Builder;
use Silk\Meta\ObjectMeta;
use Silk\Exception\WP_ErrorException;
use Silk\Post\Exception\PostNotFoundException;
use Silk\Post\Exception\ModelPostTypeMismatchException;

/**
 * @property-read $post
 * @property-read $id
 * All WP_Post properties are available via magic get/set on this instance
 * @property $ID
 * @property $comment_count
 * @property $comment_status
 * @property $filter
 * @property $guid
 * @property $menu_order
 * @property $ping_status
 * @property $pinged
 * @property $post_author
 * @property $post_content
 * @property $post_content_filtered
 * @property $post_date
 * @property $post_date_gmt
 * @property $post_excerpt
 * @property $post_mime_type
 * @property $post_modified
 * @property $post_modified_gmt
 * @property $post_name
 * @property $post_parent
 * @property $post_password
 * @property $post_status
 * @property $post_title
 * @property $post_type
 * @property $to_ping
 */
abstract class Model
{
    /**
     * The post
     * @var WP_Post
     */
    protected $post;

    /**
     * Post ID
     * @var int
     */
    protected $id;

    /**
     * The post type of the post this model wraps
     * @var string
     */
    const POST_TYPE = '';


    /**
     * Create a new instance
     *
     * @param WP_Post $post  Post object to model
     */
    public function __construct(WP_Post $post = null)
    {
        if (! $post) {
            $post = new WP_Post(new stdClass);
            $post->post_type = static::postTypeId();
        }

        $this->post = $post;
        $this->id   = $post->ID;
    }

    /**
     * Create a new instance from the given WP_Post object
     *
     * @param  WP_Post $post
     *
     * @return static
     */
    public static function fromWpPost(WP_Post $post)
    {
        if ($post->post_type !== static::postTypeId()) {
            throw new ModelPostTypeMismatchException(static::class, $post);
        }

        return new static($post);
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

        return static::fromWpPost($post);
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

        return static::fromWpPost($GLOBALS['post']);
    }

    /**
     * Create a new post of the model's type
     *
     * @param  array $attributes
     *
     * @return static
     */
    public static function create($attributes = [])
    {
        $post = new WP_Post((object)
            collect($attributes)
                ->except('ID')
                ->put('post_type', static::postTypeId())
                ->all()
        );

        return static::fromWpPost($post)->save();
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
     * Meta API for this post
     *
     * @param  string $key  Meta key to retreive or empty to retreive all.
     *
     * @return object
     */
    public function meta($key = '')
    {
        $meta = new ObjectMeta('post', $this->id);

        if ($key) {
            return $meta->get($key);
        }

        return $meta;
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
     * Permanently deletes the post and related objects
     *
     * When the post and page is permanently deleted, everything that is
     * tied to it is deleted also. This includes comments, post meta fields,
     * and terms associated with the post.
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
     * Refresh the post object from cache/database
     *
     * @return $this
     */
    public function refresh()
    {
        $this->post = WP_Post::get_instance($this->id);

        return $this;
    }

    /**
     * Update the post in the database
     *
     * @return $this
     */
    public function save()
    {
        if (! $this->id) {
            $result = wp_insert_post($this->post->to_array(), true);
        } else {
            $result = wp_update_post($this->post, true);
        }

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        $this->id = (int) $result;

        return $this->refresh();
    }

    /**
     * Get a new query builder for the model.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return (new Builder(new WP_Query))->setModel($this);
    }

    /**
     * Create a new query builder instance for this model type.
     *
     * @return Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Magic getter
     *
     * @param  string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        /**
         * WP_Post translates non-existent properties to single post meta get
         */
        return $this->post->$property;
    }

    /**
     * Magic setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        if (isset($this->post->$property)) {
            $this->post->$property = $value;
        }
    }

    /**
     * Handle dynamic static method calls on the model class.
     *
     * Proxies calls to direct method calls on a new instance
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array([new static, $method], $arguments);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $query = $this->newQuery();

        return call_user_func_array([$query, $method], $arguments);
    }

}
