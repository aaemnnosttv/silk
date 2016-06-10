<?php

namespace Silk\Query;

use WP_Query;
use Silk\Post\Post;
use Silk\Post\Model;
use Illuminate\Support\Collection;

class Builder
{
    /**
     * WP_Query instance
     * @var WP_Query
     */
    protected $query;

    /**
     * Post Model instance
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
     * [order description]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public function order($order)
    {
        $this->query->set('order', strtoupper($order));

        return $this;
    }

    /**
     * Get the results as a collection of post model instances
     *
     * @return Collection
     */
    public function results()
    {
        if ($this->model) {
            $this->query->set('post_type', $this->model->post_type);
            $this->query->set('fields', ''); // return objects
        }

        $modelClass = $this->model ? get_class($this->model) : Post::class;

        return Collection::make($this->query->get_posts())
            ->map(function ($post) use ($modelClass) {
                return new $modelClass($post);
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
