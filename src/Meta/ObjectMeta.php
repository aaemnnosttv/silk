<?php

namespace Silk\Meta;

use Illuminate\Support\Collection;

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
     * Create a new ObjectMeta instance
     * @param string $type object type
     * @param int    $id   object id
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = (int) $id;
    }

    /**
     * Get meta object for the key
     *
     * @param  string $key  meta key
     *
     * @return Silk\Models\Meta
     */
    public function get($key)
    {
        return new Meta($this->type, $this->id, $key);
    }

    /**
     * Get all meta for the object as a Collection
     *
     * @return Collection
     */
    public function collect()
    {
        return Collection::make($this->toArray());
    }

    /**
     * Get the representation of the instance as an array
     * @return array
     */
    public function toArray()
    {
        return (array) get_metadata($this->type, $this->id, '', false);
    }

    /**
     * Get the single value for the given key
     *
     * @param  string $property meta key
     * @return mixed            meta value
     */
    public function __get($property)
    {
        return (new Meta($this->type, $this->id, $property))->get();
    }
}
