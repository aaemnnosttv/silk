<?php

namespace Silk\Type;

trait ShorthandProperties
{
    protected $object;

    /**
     * Expands an alias into its respective object property name.
     *
     * @param string $key  Alias key
     *
     * @return mixed|string
     */
    protected function expandAlias($key)
    {
        if (is_object($this->object) || ! empty(static::OBJECT_TYPE)) {
            /**
             * Automatically alias shorthand syntax for type_name
             * Eg: 'post_content' is aliased to 'content'
             */
            $expanded = static::OBJECT_TYPE . '_' . $key;

            if (property_exists($this->object, $expanded)) {
                return $expanded;
            }
        }

        return parent::expandAlias($key);
    }
}
