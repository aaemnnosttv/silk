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
}
