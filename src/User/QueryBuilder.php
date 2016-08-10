<?php

namespace Silk\User;

use WP_User_Query;
use Silk\Query\Builder as BaseBuilder;
use Illuminate\Support\Collection;

class QueryBuilder extends BaseBuilder
{
    /**
     * The query instance
     * @var WP_User_Query
     */
    protected $query;

    /**
     * QueryBuilder Constructor.
     *
     * @param WP_User_Query $query
     */
    public function __construct(WP_User_Query $query = null)
    {
        if (! $query) {
            $query = new WP_User_Query();
        }

        $this->query = $query;
    }

    /**
     * Create a new instance.
     *
     * @param WP_User_Query $query
     *
     * @return static
     */
    public static function make(WP_User_Query $query = null)
    {
        return new static($query);
    }

    /**
     * Get the query results.
     *
     * @return Collection
     */
    public function results()
    {
        return Collection::make($this->query->get_results());
    }
}
