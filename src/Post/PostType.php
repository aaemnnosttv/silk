<?php

namespace Silk\Post;

use stdClass;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Silk\Exception\WP_ErrorException;
use Silk\Post\Exception\InvalidPostTypeNameException;
use Silk\Post\Exception\NonExistentPostTypeException;
use Silk\Post\PostTypeBuilder;

/**
 * @property-read string $slug
 * @property-read string $one
 * @property-read string $many
 */
class PostType
{
    /**
     * Post type object
     * @var stdClass
     */
    protected $object;

    /**
     * PostType Constructor
     *
     * @param stdClass $object  The WordPress post type object
     */
    public function __construct(stdClass $object)
    {
        $this->object = $object;
    }

    /**
     * Create a new instance using the post type slug.
     *
     * Loads an existing type, or returns a new builder for registering a new type.
     *
     * @param  string $slug  The post type slug
     *
     * @return static|PostTypeBuilder  If the post type has been registered, a new static instance is returned.
     *                                 Otherwise a new PostTypeBuilder is created for building a new post type to register.
     */
    public static function make($slug)
    {
        if (static::exists($slug)) {
            return static::load($slug);
        }

        return new PostTypeBuilder($slug);
    }

    /**
     * Create a new instance from an existing type.
     *
     * @param  string $slug  The post type slug
     *
     * @return static
     */
    public static function load($slug)
    {
        if (! $object = get_post_type_object($slug)) {
            throw new NonExistentPostTypeException("No post type exists with name '$slug'.");
        }

        return new static($object);
    }

    /**
     * Checks if a post type with this slug has been registered.
     *
     * @param string $slug  The post type slug
     *
     * @return bool
     */
    public static function exists($slug)
    {
        return post_type_exists($slug);
    }

    /**
     * Get the post type object.
     *
     * @return object
     */
    public function object()
    {
        return $this->object;
    }

    /**
     * Check for feature support.
     *
     * @param string,...|array $features  string - First feature of possible many,
     *                                    array - Many features to check support for.
     *
     * @return mixed
     */
    public function supports($features)
    {
        if (! is_array($features)) {
            $features = func_get_args();
        }

        return ! collect($features)
            ->contains(function ($key, $feature) {
                return ! post_type_supports($this->slug, $feature);
            });
    }

    /**
     * Register support of certain features for an existing post type.
     *
     * @param mixed $features  string - single feature to add
     *                        array - multiple features to add
     */
    public function addSupportFor($features)
    {
        add_post_type_support($this->slug, is_array($features) ? $features : func_get_args());

        return $this;
    }

    /**
     * Deregister support of certain features for an existing post type.
     *
     * @param mixed $features  string - single feature to remove
     *                        array - multiple features to remove
     */
    public function removeSupportFor($features)
    {
        collect(is_array($features) ? $features : func_get_args())
            ->each(function ($features) {
                remove_post_type_support($this->slug, $features);
            });

        return $this;
    }

    /**
     * Unregister the post type
     *
     * @return $this
     */
    public function unregister()
    {
        if (! static::exists($this->slug)) {
            throw new NonExistentPostTypeException("No post type exists with name '{$this->slug}'.");
        }

        $result = unregister_post_type($this->slug);

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        return $this;
    }

    /**
     * Magic Getter
     *
     * @param  string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        switch ($property) :
            case 'slug':
                return $this->object->name;
            case 'one':
                return $this->object->labels->singular_name;
            case 'many':
                return $this->object->labels->name;
        endswitch;

        return null;
    }
}
