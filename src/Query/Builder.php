<?php

namespace Silk\Query;

use WP_Query;
use Illuminate\Support\Collection;

class Builder
{
    /**
     * WP_Query instance
     * @var \WP_Query
     */
    protected $query;

    public function __construct(WP_Query $query)
    {
        $this->query = $query;
    }

    public function limit($limit)
    {
        $this->query->set('posts_per_page', (int) $limit);

        return $this;
    }

    public function results()
    {
        return Collection::make($this->query->get_posts());
    }
}
