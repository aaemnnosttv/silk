<?php

namespace Silk\User;

use WP_User_Query;
use Silk\Query\Builder as BaseBuilder;

/**
 * @property Model $model
 */
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
     * Execute the query and return the raw results.
     *
     * @return array
     */
    protected function query()
    {
        $this->set('fields', 'all');

        $this->query->prepare_query();
        $this->query->query();

        return $this->query->get_results();
    }

    /**
     * Set an arbitrary query parameter.
     *
     * @param $parameter
     * @param $value
     *
     * @return $this
     */
    public function set($parameter, $value)
    {
        $this->query->set($parameter, $value);

        return $this;
    }
}
