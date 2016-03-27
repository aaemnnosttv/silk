<?php

namespace Silk\Models;

use stdClass;
use WP_Post;
use Illuminate\Support\Collection;
use Silk\WP_ErrorException;
use Silk\Meta\ObjectMeta;
use Silk\Models\Exceptions\PostNotFoundException;
use Silk\Models\Exceptions\ModelPostTypeMismatchException;

class Post
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
            $post = new WP_Post(new StdClass);
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
            'post_status'    => 'any',
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
        unset($attributes['ID']);

        $post = new WP_Post((object) $attributes);
        $model = static::fromWpPost($post);

        return $model->save();
    }

    /**
     * Meta API for this post
     *
     * @param  string $key [description]
     * @return Meta
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
            $result = wp_insert_post($this->post, true);
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
     * [__get description]
     * @param  [type] $property [description]
     * @return [type]           [description]
     */
    public function __get($property)
    {
        if ('id' === $property) {
            return $this->id;
        }

        if ('type' === $property) {
            return static::POST_TYPE;
        }

        if (isset($this->$property)) {
            return $this->$property;
        }

        if (isset($this->post->$property)) {
            return $this->post->$property;
        }

        throw new \InvalidArgumentException(static::class . " has no property: {$property}");
    }

    /**
     * [__set description]
     * @param [type] $property [description]
     * @param [type] $value    [description]
     */
    public function __set($property, $value)
    {
        if (isset($this->post->$property)) {
            $this->post->$property = $value;
        }
    }
}
