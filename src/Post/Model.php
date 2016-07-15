<?php

namespace Silk\Post;

use stdClass;
use WP_Post;
use WP_Query;
use Illuminate\Support\Collection;
use Silk\Type\Model as BaseModel;
use Silk\PostType\PostType;
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
     * @param WP_Post $post  Post object to model
     */
    public function __construct(WP_Post $post = null)
    {
        if (! $post) {
            $post = new WP_Post(new stdClass);
            $post->post_type = static::postTypeId();
        }

        $this->object = $post;
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
            Collection::make($attributes)
                ->except(static::ID_PROPERTY)
                ->put('post_type', static::postTypeId())
                ->all()
        );

        return static::fromWpPost($post)->save();
    }

    /**
     * Get the post type identifier for this model.
     *
     * @return string
     */
    public static function typeId()
    {
        return static::postTypeId();
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
        return (new QueryBuilder(new WP_Query))->setModel($this);
    }

    /**
     * Get the array of actions and their respective handler classes.
     *
     * @return array
     */
    protected function actionClasses()
    {
        return [
            'save'   => Action\PostSaver::class,
            'load'   => Action\PostLoader::class,
            'delete' => Action\PostDeleter::class,
        ];
    }
}
