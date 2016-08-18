<?php

namespace Silk\Post;

use WP_Query;
use Silk\Query\Builder as BaseBuilder;

/**
 * @property Model $model
 */
class QueryBuilder extends BaseBuilder
{
    /**
     * WP_Query instance
     *
     * @var WP_Query
     */
    protected $query;

    /**
     * Builder constructor.
     *
     * @param WP_Query $query
     */
    public function __construct(WP_Query $query = null)
    {
        if (! $query) {
            $query = new WP_Query();
        }

        $this->query = $query;
    }

    /**
     * Create a new instance.
     *
     * @param WP_Query $query
     *
     * @return static
     */
    public static function make(WP_Query $query = null)
    {
        return new static($query);
    }

    /**
     * Limit the number of returned results
     *
     * @param integer $limit  The maximum number of results to return
     *                        use -1 for no limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        return $this->set('posts_per_page', (int) $limit);
    }

    /**
     * Return an unlimited number of results.
     *
     * @return $this
     */
    public function all()
    {
        return $this->limit(-1);
    }

    /**
     * Set the order for the query
     *
     * @param  string $order
     *
     * @return $this
     */
    public function order($order)
    {
        return $this->set('order', strtoupper($order));
    }

    /**
     * Query by post status
     *
     * @param  string|array $status  the post status or stati to match
     *
     * @return $this
     */
    public function whereStatus($status)
    {
        return $this->set('post_status', $status);
    }

    /**
     * Query by slug
     *
     * @param  string $slug  the post slug to query by
     *
     * @return $this
     */
    public function whereSlug($slug)
    {
        return $this->set('name', $slug);
    }

    /**
     * Set a query variable on the query
     *
     * @param string $var   Query variable key
     * @param mixed  $value Query value for key
     *
     * @return $this
     */
    public function set($var, $value)
    {
        $this->query->set($var, $value);

        return $this;
    }

    /**
     * Execute the query and return the raw results.
     *
     * @return array
     */
    protected function query()
    {
        if ($this->model) {
            $this->set('post_type', $this->model->post_type)
                 ->set('fields', ''); // as WP_Post objects
        }

        return $this->query->get_posts();
    }
}
