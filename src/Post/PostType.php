<?php

namespace Silk\Post;

use stdClass;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Silk\Exception\WP_ErrorException;
use Silk\Post\Exception\InvalidPostTypeNameException;
use Silk\Post\Exception\NonExistentPostTypeException;

class PostType
{
    protected $slug;

    protected $object;

    /**
     * Registration Arguments
     * @var Collection
     */
    protected $args;

    /**
     * [__construct description]
     * @param mixed $type   string - post type slug
     *                      object - post type object
     */
    public function __construct($type, array $args = [])
    {
        if (isset($type->name)) {
            $this->slug   = $type->name;
            $this->object = $type;
        } elseif (is_string($type)) {
            $this->slug   = $type;
            $this->object = new stdClass;
        } else {
            throw new InvalidArgumentException('Post type must be a string (slug) or post type object.');
        }

        $this->args = Collection::make($args)->put('name', $this->slug);

        if (strlen($this->slug) < 1 || strlen($this->slug) > 20) {
            throw new InvalidPostTypeNameException('Post type names must be between 1 and 20 characters in length.');
        }
    }

    /**
     * Create a new instance
     *
     * @param  string|object $slug  the post type slug ("name")
     *
     * @return static
     */
    public static function make($slug)
    {
        return new static($slug);
    }

    /**
     * Create a new instance from an existing type
     *
     * @param  string $slug  post type id  (slug/name)
     *
     * @return static
     */
    public static function load($slug)
    {
        if (! $object = get_post_type_object($slug)) {
            throw new NonExistentPostTypeException("No post type exists with name '$slug'.");
        }

        return static::make($object);
    }

    /**
     * [object description]
     *
     * @return object
     */
    public function object()
    {
        return $this->object;
    }

    /**
     * [supports description]
     *
     * @return $this
     */
    public function supports($features)
    {
        if (! is_array($features)) {
            $features = func_get_args();
        }

        $this->merge('supports', $features);

        return $this;
    }

    /**
     * Set the post type as publicly available
     *
     * @return $this
     */
    public function open()
    {
        $this->set('public', true);

        return $this;
    }

    /**
     * Set the post type as non-publicly available
     *
     * @return $this
     */
    public function closed()
    {
        $this->set('public', false);

        return $this;
    }

    /**
     * Enable admin interface
     *
     * @return $this
     */
    public function withUI()
    {
        $this->set('show_ui', true);

        return $this;
    }

    /**
     * Disable admin interface
     *
     * @return $this
     */
    public function noUI()
    {
        $this->set('show_ui', false);

        return $this;
    }

    /**
     * Register the post type
     *
     * @return $this
     */
    public function register()
    {
        $this->object = register_post_type($this->slug, $this->args->toArray());

        return $this;
    }

    /**
     * [unregister description]
     * @return [type] [description]
     */
    public function unregister()
    {
        if (! post_type_exists($this->slug)) {
            throw new NonExistentPostTypeException("No post type exists with name '{$this->slug}'.");
        }

        $result = unregister_post_type($this->slug);

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        return $this;
    }

    /**
     * [set description]
     * @param [type] $key   [description]
     * @param [type] $value [description]
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->args->put($key, $value);

        return $this;
    }

    /**
     * [set description]
     * @param [type] $key   [description]
     */
    public function get($key, $default = null)
    {
        return $this->args->get($key, $default);
    }

    /**
     * [merge description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function merge($key, $value)
    {
        $currentValue = collect($this->args->get($key, []));

        $this->args->put($key, $currentValue->merge($value)->all());

        return $this;
    }
}
