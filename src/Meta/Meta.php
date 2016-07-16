<?php

namespace Silk\Meta;

use Illuminate\Support\Collection;

class Meta
{
    /**
     * Meta type
     * @var string
     */
    protected $type;

    /**
     * The object ID this metadata is for
     * @var int
     */
    protected $object_id;

    /**
     * The key the metadata is for
     * @var string
     */
    protected $key;

    /**
     * Meta Constructor.
     *
     * @param string     $type      Meta type
     * @param int|string $object_id ID of the object metadata is for
     * @param string     $key       Meta key
     */
    public function __construct($type, $object_id, $key)
    {
        $this->type      = $type;
        $this->object_id = (int) $object_id;
        $this->key       = $key;
    }

    /**
     * Get the single metadata.
     *
     * @return mixed
     */
    public function get()
    {
        return get_metadata($this->type, $this->object_id, $this->key, true);
    }

    /**
     * Get all metadata as a Collection.
     *
     * @return Collection
     */
    public function collect()
    {
        return Collection::make($this->all());
    }

    /**
     * Get all metadata as an array.
     *
     * @return array
     */
    public function all()
    {
        return (array) get_metadata($this->type, $this->object_id, $this->key, false);
    }

    /**
     * Set the new value.
     *
     * @param mixed  $value
     * @param string $prev_value
     *
     * @return $this
     */
    public function set($value, $prev_value = '')
    {
        update_metadata($this->type, $this->object_id, $this->key, $value, $prev_value);

        return $this;
    }

    /**
     * Replace a single value.
     *
     * @param  mixed $old  Previous value to update
     * @param  mixed $new  New value to set the previous value to
     *
     * @return $this
     */
    public function replace($old, $new)
    {
        return $this->set($new, $old);
    }

    /**
     * Add metadata for the specified object.
     *
     * @param mixed  $value   The value to add
     * @param bool   $unique  Whether the specified metadata key should be unique
     *                        for the object.  If true, and the object already has
     *                        a value for the specified metadata key, no change will be made.
     *
     * @return $this
     */
    public function add($value, $unique = false)
    {
        add_metadata($this->type, $this->object_id, $this->key, $value, $unique);

        return $this;
    }

    /**
     * Delete the metadata
     *
     * Deletes all metadata for the key, if provided, optionally filtered by
     * a previous value.
     * If no key was provided, all metadata for the object is deleted.
     *
     * @param  string $value The old value to delete.
     *                       This is only necessary when deleting a specific value
     *                       from an object which has multiple values for the key.
     *
     * @return $this
     */
    public function delete($value = '')
    {
        delete_metadata($this->type, $this->object_id, $this->key, $value);

        return $this;
    }

    /**
     * Determine if a meta key is set for a given object.
     *
     * @return bool     True of the key is set, false if not.
     */
    public function exists()
    {
        return metadata_exists($this->type, $this->object_id, $this->key);
    }

    /**
     * Get the metadata as a string.
     *
     * @return string  The meta value
     */
    public function __toString()
    {
        return $this->get();
    }
}
