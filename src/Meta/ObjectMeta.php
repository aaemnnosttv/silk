<?php

namespace Silk\Meta;

use Illuminate\Support\Collection;

/**
 * @property-read string $type
 * @property-read int $id
 */
class ObjectMeta
{
    /**
     * Object type
     * @var string
     */
    protected $type;

    /**
     * Object ID
     * @var int
     */
    protected $id;

    /**
     * ObjectMeta constructor.
     *
     * @param string $type Object type
     * @param int    $id   Object ID
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = (int) $id;
    }

    /**
     * Get meta object for the key.
     *
     * @param  string $key  meta key
     *
     * @return Meta
     */
    public function get($key)
    {
        return new Meta($this->type, $this->id, $key);
    }

    /**
     * Get all meta for the object as a Collection.
     *
     * @return Collection
     */
    public function collect()
    {
        return Collection::make($this->toArray())->map(function ($value, $key) {
            return new Meta($this->type, $this->id, $key);
        });
    }

    /**
     * Get the representation of the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) get_metadata($this->type, $this->id, '', false);
    }

    /**
     * Magic Getter.
     *
     * @param  string $property Accessed property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return isset($this->$property) ? $this->$property : null;
    }
}
