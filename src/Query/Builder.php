<?php

namespace Silk\Query;

use Silk\Type\Model;
use Silk\Contracts\Query\BuildsQueries;
use Illuminate\Support\Collection;

/**
 * @property-read object $query
 * @property-read Model  $model
 */
abstract class Builder implements BuildsQueries
{
    /**
     * The query instance
     * @var object  The query instance
     */
    protected $query;

    /**
     * @var Model|null  The model instance if set, otherwise null
     */
    protected $model;

    /**
     * Execute the query and return the raw results.
     *
     * @return array
     */
    abstract protected function query();

    /**
     * Get the results as a collection
     *
     * @return Collection
     */
    public function results()
    {
        if ($this->model) {
            return $this->collectModels();
        }

        return new Collection($this->query());
    }

    /**
     * Get the results as a collection of model instances.
     *
     * @return Collection
     */
    protected function collectModels()
    {
        $modelClass = get_class($this->model);

        return Collection::make($this->query())
                         ->map(function ($result) use ($modelClass) {
                             return new $modelClass($result);
                         });
    }

    /**
     * Set the model for this query.
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
}
