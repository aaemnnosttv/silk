<?php

namespace Silk\Contracts\Query;

use Illuminate\Support\Collection;

interface BuildsQueries
{
    /**
     * Set an arbitrary query parameter.
     *
     * @param $parameter
     * @param $value
     *
     * @return $this
     */
    public function set($parameter, $value);

    /**
     * Get the results of the query.
     *
     * @return Collection
     */
    public function results();
}
