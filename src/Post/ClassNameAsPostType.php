<?php

namespace Silk\Post;

trait ClassNameAsPostType
{
    /**
     * Class name derived post type identifiers
     * @var array  className => post_type
     */
    protected static $classNamePostType = [];

    /**
     * Get the post type identifier for this model
     *
     * This method overloads the constant-based default, allowing for
     * a convenient alternative to hard-coding the post type.
     * The POST_TYPE constant must take precedence over the derived name, if set.
     *
     * @return string post type identifier (slug)
     */
    public static function postTypeId()
    {
        if (static::POST_TYPE) {
            return static::POST_TYPE;
        }

        return static::getPostTypeFromName();
    }

    /**
     * Get the post type id from the class name
     *
     * @return string
     */
    protected static function getPostTypeFromName()
    {
        if (isset(static::$classNamePostType[static::class])) {
            return static::$classNamePostType[static::class];
        }

        /**
         * Convert the class name to snake_case and cache on a static property
         * to prevent evaluating more than once.
         */
        $name = (new \ReflectionClass(static::class))->getShortName();

        /**
         * Adapted from Str::snake()
         * @link https://github.com/laravel/framework/blob/5.2/src/Illuminate/Support/Str.php
         */
        if (! ctype_lower($name)) {
            $name = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $name));
        }

        return static::$classNamePostType[static::class] = $name;
    }
}
