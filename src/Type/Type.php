<?php

namespace Silk\Type;

/**
 * @property-read int|string $id
 * @property-read string     $slug
 * @property-read string     $one
 * @property-read string     $many
 */
abstract class Type
{
    /**
     * The type object
     * @var object
     */
    protected $object;

    /**
     * Type object property aliases
     * @var array
     */
    protected $objectAliases = [
        'id'   => 'name',
        'slug' => 'name',
        'one'  => 'labels.singular_name',
        'many' => 'labels.name',
    ];

    /**
     * Get the type object.
     *
     * @return object
     */
    public function object()
    {
        return $this->object;
    }

    /**
     * Magic Getter.
     *
     * @param  string $property  Accessed property name
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (isset($this->object->$property)) {
            return $this->object->$property;
        }

        if (! array_key_exists($property, $this->objectAliases)) {
            return null;
        }

        return data_get($this->object, $this->objectAliases[$property]);
    }

    /**
     * Magic Isset Check.
     *
     * @param  string  $property Queried property name
     *
     * @return boolean
     */
    public function __isset($property)
    {
        return ! is_null($this->__get($property));
    }
}
