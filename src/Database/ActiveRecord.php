<?php

namespace Silk\Database;

use Silk\Meta\ObjectMeta;
use Silk\Contracts\Executable;
use Illuminate\Support\Collection;

/**
 * @property-read int    $id
 * @property-read object $object
 */
abstract class ActiveRecord
{
    /**
     * The core model object
     * @var object
     */
    protected $object;

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
     * @return BuildsQueries
     */
    abstract public function newQuery();

    /**
     * Get the map of action => class for resolving active actions.
     *
     * @return array
     */
    abstract protected function actionClasses();

    /**
     * Create a new query builder instance for this model type.
     *
     * @return BuildsQueries
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Save the changes to the database.
     *
     * @return $this
     */
    public function save()
    {
        $this->activeAction('save');

        return $this;
    }

    /**
     * Delete the record from the database.
     *
     * @return $this
     */
    public function delete()
    {
        $this->activeAction('delete');

        return $this;
    }

    /**
     * Load and set the object from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->activeAction('load');

        return $this;
    }

    /**
     * Meta API for this type
     *
     * @param  string $key  Meta key to retreive or empty to retreive all.
     *
     * @return object
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
     * Update the core object
     *
     * @param object $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Set the primary ID on the model.
     *
     * @param string|int $id  The model's ID
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->object->{static::ID_PROPERTY} = (int) $id;

        return $this;
    }

    /**
     * Perform a database action.
     *
     * @return void
     */
    protected function activeAction($action)
    {
        $actionClass = Collection::make(
            $this->actionClasses()
        )->get($action, NullAction::class);

        $this->executeAction(new $actionClass($this));
    }

    /**
     * Execute the active action
     *
     * @return void
     */
    protected function executeAction(Executable $action)
    {
        $action->execute();
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

        if ($property == static::OBJECT_TYPE) {
            return $this->object;
        }

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
        if (property_exists($this->object, $property)) {
            $this->object->$property = $value;
        }
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
