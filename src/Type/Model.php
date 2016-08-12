<?php

namespace Silk\Type;

use Silk\Meta\ObjectMeta;
use Illuminate\Support\Collection;

/**
 * @property-read int    $id
 * @property-read object $object
 */
abstract class Model
{
    /**
     * The core model object
     * @var object
     */
    protected $object;

    /**
     * Type object property aliases
     * @var array
     */
    protected $objectAliases = [
        // 'aliasName' => 'propertyNameOnObject'
    ];

    /**
     * The object type in WordPress
     */
    const OBJECT_TYPE = '';

    /**
     * The name of the primary ID property on the object
     */
    const ID_PROPERTY = '';

    /**
    * Get a new query builder for the model.
    *
    * @return \Silk\Contracts\BuildsQueries
    */
    abstract public function newQuery();

    /**
     * Save the changes to the database.
     *
     * @return $this
     */
    abstract public function save();

    /**
     * Delete the modeled record from the database.
     *
     * @return $this
     */
    abstract public function delete();

    /**
     * Reload the object from the database.
     *
     * @return $this
     */
    abstract public function refresh();

    /**
     * Make new instance.
     *
     * All provided arguments are forwarded to the constructor of the called class.
     *
     * @return static
     */
    public static function make()
    {
        if ($arguments = func_get_args()) {
            return (new \ReflectionClass(static::class))->newInstanceArgs($arguments);
        }

        return new static;
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     *
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $expanded = $this->expandAlias($key);
            $this->object->$expanded = $value;
        }

        return $this;
    }

    /**
     * Create a new model of the model's type, and save it to the database.
     *
     * @param  array $attributes
     *
     * @return static
     */
    public static function create($attributes = [])
    {
        $model = new static($attributes);

        return $model->save();
    }

    /**
     * Create a new query builder instance for this model type.
     *
     * @return \Silk\Contracts\BuildsQueries
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Meta API for this type
     *
     * @param  string $key  Meta key to retrieve or empty to retrieve all.
     *
     * @return ObjectMeta|\Silk\Meta\Meta
     */
    public function meta($key = '')
    {
        $meta = new ObjectMeta(static::OBJECT_TYPE, $this->id);

        if ($key) {
            return $meta->get($key);
        }

        return $meta;
    }

    /**
     * Set the primary ID on the model.
     *
     * @param string|int $id  The model's ID
     *
     * @return $this
     */
    protected function setId($id)
    {
        $this->object->{static::ID_PROPERTY} = (int) $id;

        return $this;
    }

    /**
     * Expands an alias into its respective object property name.
     *
     * @param string $key  Alias key
     *
     * @return mixed|string
     */
    protected function expandAlias($key)
    {
        if (isset($this->objectAliases[$key])) {
            return $this->objectAliases[$key];
        }

        return $key;
    }

    /**
     * Magic getter.
     *
     * @param  string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($property == 'id') {
            return $this->object->{static::ID_PROPERTY};
        }

        if (in_array($property, ['object', static::OBJECT_TYPE])) {
            return $this->object;
        }

        $property = $this->expandAlias($property);

        /**
         * Finally, hand-off the request to the wrapped object.
         * We don't check for existence as we leverage the magic __get
         * on the wrapped object as well.
         */
        return $this->object->$property;
    }

    /**
     * Magic Isset Checker.
     *
     * @return bool
     */
    public function __isset($property)
    {
        return ! is_null($this->__get($property));
    }

    /**
     * Magic setter.
     *
     * @param string $property  The property name
     * @param mixed  $value     The new property value
     */
    public function __set($property, $value)
    {
        $property = $this->expandAlias($property);

        $this->object->$property = $value;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $query = $this->newQuery();

        return call_user_func_array([$query, $method], $arguments);
    }

    /**
     * Handle dynamic static method calls on the model class.
     *
     * Proxies calls to direct method calls on a new instance
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array([new static, $method], $arguments);
    }
}
