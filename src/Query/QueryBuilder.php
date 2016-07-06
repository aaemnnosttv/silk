<?php

namespace Silk\Query;

use Silk\Contracts\BuildsQueries;

trait QueryBuilder
{
    /**
     * Get a new query builder for the model.
     *
     * @return BuildsQueries
     */
    abstract public function newQuery();

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
}
