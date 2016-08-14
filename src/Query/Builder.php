<?php

namespace Silk\Query;

use Silk\Type\Model;
use Silk\Contracts\BuildsQueries;

abstract class Builder implements BuildsQueries
{
    /**
     * @var Model  The model instance
     */
    protected $model;

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
