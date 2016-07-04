<?php

namespace Silk\Contracts;

use Illuminate\Support\Collection;

interface BuildsQueries
{
    /**
     * Set the model for this query.
     *
     * @param mixed $model
     *
     * @return $this
     */
    public function setModel($model);

    /**
     * Get the model.
     *
     * @return mixed Model
     */
    public function getModel();

    /**
     * Get the results of the query.
     *
     * @return Collection
     */
    public function results();
}
