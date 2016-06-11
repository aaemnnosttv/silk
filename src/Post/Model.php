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
    const POST_TYPE = 'post';


    /**
     * [__construct description]
     * @param WP_Post $post [description]
     */
    public function __construct(WP_Post $post = null)
    {
        if (! $post) {
            $post = new WP_Post(new stdClass);
            $post->post_type = static::POST_TYPE;
        }

        $this->post = $post;
        $this->id   = $post->ID;
    }

    public static function fromWpPost(WP_Post $post)
    {
        if ($post->post_type !== static::POST_TYPE) {
            throw new ModelPostTypeMismatchException(static::class, $post);
        }

        return new static($post);
    }

    /**
     * Make new instance from a Post with the given ID
     *
     * @param  int|string $id [description]
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
     * Make new instance from a Post slug
     *
     * @param  string $slug  the post slug
     *
     * @return static
     */
    public static function fromSlug($slug)
    {
        $posts = (array) get_posts([
            'name'           => $slug,
            'post_type'      => static::POST_TYPE,
            'posts_per_page' => 1
        ]);

        if (! $post = reset($posts)) {
            throw new PostNotFoundException("No post found with slug {$slug}");
        }

        return static::fromWpPost($post);
    }

    /**
     * Make new instance from the global $post
     *
     * @return static
     */
    public static function fromGlobal()
    {
        $post = get_post();

        if (! $post instanceof WP_Post) {
            throw new PostNotFoundException('Global $post not an instance of WP_Post');
        }

        return static::fromWpPost($post);
    }

    /**
     * Create a new post of the model's type
     *
     * @param  [type] $attributes [description]
     * @return [type]             [description]
     */
    public static function create($attributes = [])
    {
        $attributes = (object) collect($attributes)->except('ID')
            ->put('post_type', static::POST_TYPE)->all();

        $post = new WP_Post($attributes);
        $model = static::fromWpPost($post);

        return $model->save();
    }

    /**
     * Meta API for this post
     *
     * @param  string $key [description]
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
     * @return false|array|WP_Post|null Post data array, otherwise false.
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
     * @return WP_Post|false WP_Post object. False on failure.
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
     * @return [type] [description]
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
     * @return static
     */
    public function refresh()
    {
        $this->post = WP_Post::get_instance($this->id);

        return $this;
    }

    /**
     * Update the post in the database
     *
     * @return [type] [description]
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

    public static function all()
    {
        return static::query()->limit(-1);
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
     * @return Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Magic getter
     * @param  string $property
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
     * @param       $method
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
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $query = $this->newQuery();

        return call_user_func_array([$query, $method], $arguments);
    }

}
