<?php

namespace Silk\Post;

use WP_Query;
use Illuminate\Support\Collection;
use Silk\Contracts\BuildsQueries;

class QueryBuilder implements BuildsQueries
{
    /**
     * WP_Query instance
     *
     * @var WP_Query
     */
    protected $query;

    /**
     * Post Model instance
     *
     * @var Model
     */
    protected $model;

    /**
     * Builder constructor.
     *
     * @param WP_Query $query
     */
    public function __construct(WP_Query $query)
    {
        $this->query = $query;
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
        $this->query->set('posts_per_page', (int) $limit);

        return $this;
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
        $this->query->set('order', strtoupper($order));

        return $this;
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
        $this->query->set('post_status', $status);

        return $this;
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
        $this->query->set('name', $slug);

        return $this;
    }

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

        return Collection::make($this->query->get_posts());
    }

    /**
     * Get the results as a collection of post model instances
     *
     * @return Collection
     */
    protected function collectModels()
    {
        $this->query->set('post_type', $this->model->post_type);
        $this->query->set('fields', ''); // as WP_Post objects
        $modelClass = get_class($this->model);

        return Collection::make($this->query->get_posts())
            ->map(function ($post) use ($modelClass) {
                return new $modelClass($post);
            });
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
