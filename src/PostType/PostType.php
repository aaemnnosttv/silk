<?php

namespace Silk\PostType;

use stdClass;
use Silk\Type\Type;
use Silk\Contracts\Type\Registerable;
use Illuminate\Support\Collection;
use Silk\Exception\WP_ErrorException;
use Silk\PostType\Exception\NonExistentPostTypeException;

class PostType extends Type implements Registerable
{
    /**
     * PostType Constructor
     *
     * @param stdClass $object  The WordPress post type object
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($object)
    {
        if (! is_object($object) || ! in_array(get_class($object), ['stdClass', 'WP_Post_Type'])) {
            throw new \InvalidArgumentException(static::class . ' can only be constructed with a Post Type object.');
        }

        $this->object = $object;
    }

    /**
     * Create a new instance using the post type slug.
     *
     * Loads an existing type, or returns a new builder for registering a new type.
     *
     * @param  string $id The post type identifier
     *
     * @return static|Builder  If the post type has been registered, a new static instance is returned.
     *                         Otherwise a new Builder is created for building a new post type to register.
     */
    public static function make($id)
    {
        if (static::exists($id)) {
            return static::load($id);
        }

        return static::build($id);
    }

    /**
     * Create a new instance from an existing type.
     *
     * @param  string $id  The post type identifier
     *
     * @return static
     */
    public static function load($id)
    {
        if (! $object = get_post_type_object($id)) {
            throw new NonExistentPostTypeException("No post type exists with name '$id'.");
        }

        return new static($object);
    }


    /**
     * Build a new type to be registered.
     *
     * @param $id
     *
     * @return mixed
     */
    public static function build($id)
    {
        return new Builder($id);
    }

    /**
     * Get the post type identifier (aka: name/slug).
     */
    public function id()
    {
        return $this->object->name;
    }

    /**
     * Checks if a post type with this slug has been registered.
     *
     * @param string $id The post type identifier
     *
     * @return bool
     */
    public static function exists($id)
    {
        return post_type_exists($id);
    }

    /**
     * Check for feature support.
     *
     * @param string|array $features  string - First feature of possible many,
     *                                array - Many features to check support for.
     *
     * @return mixed
     */
    public function supports($features)
    {
        if (! is_array($features)) {
            $features = func_get_args();
        }

        return ! Collection::make($features)
            ->contains(function ($key, $feature) {
                return ! post_type_supports($this->id(), $feature);
            });
    }

    /**
     * Register support of certain features for an existing post type.
     *
     * @param mixed $features string - single feature to add
     *                        array - multiple features to add
     *
     * @return $this
     */
    public function addSupportFor($features)
    {
        add_post_type_support($this->id(), is_array($features) ? $features : func_get_args());

        return $this;
    }

    /**
     * Un-register support of certain features for an existing post type.
     *
     * @param mixed $features string - single feature to remove
     *                        array - multiple features to remove
     *
     * @return $this
     */
    public function removeSupportFor($features)
    {
        Collection::make(is_array($features) ? $features : func_get_args())
            ->each(function ($features) {
                remove_post_type_support($this->id(), $features);
            });

        return $this;
    }

    /**
     * Unregister the post type.
     *
     * @throws NonExistentPostTypeException
     * @throws WP_ErrorException
     *
     * @return $this
     */
    public function unregister()
    {
        $id = $this->id();

        if (! static::exists($id)) {
            throw new NonExistentPostTypeException("No post type exists with name '{$id}'.");
        }

        if (is_wp_error($error = unregister_post_type($id))) {
            throw new WP_ErrorException($error);
        }

        return $this;
    }
}
