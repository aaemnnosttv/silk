<?php

namespace Silk\Contracts;

use Illuminate\Support\Collection;

interface BuildsQueries
{
    /**
     * Get the results of the query.
     *
     * @return Collection
     */
    public function results();
}
