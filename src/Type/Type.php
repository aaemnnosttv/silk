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
    use ObjectAliases;

    /**
     * The type object
     * @var object
     */
    protected $object;

    /**
     * @return array
     */
    protected function objectAliases()
    {
        return [
            'id'   => 'name',
            'slug' => 'name',
            'one'  => 'labels.singular_name',
            'many' => 'labels.name',
        ];
    }

    /**
     * @return object
     */
    protected function getAliasedObject()
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
        if (! is_null($aliased = $this->aliasGet($property))) {
            return $aliased;
        }

        return data_get($this->object, $property);
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
