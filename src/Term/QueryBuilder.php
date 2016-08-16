<?php

namespace Silk\Term;

use Silk\Query\Builder as BaseBuilder;
use Silk\Exception\WP_ErrorException;
use Illuminate\Support\Collection;

/**
 * @property Model $model
 */
class QueryBuilder extends BaseBuilder
{
    /**
     * Query arguments
     * @var Collection
     */
    protected $query;

    /**
     * Taxonomy Identifier
     * @var string
     */
    protected $taxonomy;

    /**
     * QueryBuilder Constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->query = new Collection($args);
    }

    /**
     * Create a new instance.
     *
     * @return static
     */
    public static function make()
    {
        return new static;
    }

    /**
     * Restrict the query to terms of the provided Taxonomy.
     *
     * @param  string $taxonomy
     *
     * @return $this
     */
    public function forTaxonomy($taxonomy)
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * Get all terms.
     *
     * @return $this
     */
    public function all()
    {
        return $this->includeEmpty()
            ->limit('all');
    }

    /**
     * Include terms that have no related objects in the results.
     *
     * @return $this
     */
    public function includeEmpty()
    {
        return $this->set('hide_empty', false);
    }

    /**
     * Limit the maximum number of results returned.
     *
     * @param  int $max_results  Maximum number to return. 0 or 'all' for unlimited.
     *
     * @return $this
     */
    public function limit($max_results)
    {
        return $this->set('number', intval($max_results));
    }

    /**
     * Execute the query and return the raw results.
     *
     * @throws WP_ErrorException
     *
     * @return array
     */
    protected function query()
    {
        if ($this->model) {
            $this->set('taxonomy', $this->model->taxonomy)
                 ->set('fields', 'all');
        } elseif ($this->taxonomy) {
            $this->set('taxonomy', $this->taxonomy);
        }

        if (is_wp_error($terms = get_terms($this->query->toArray()))) {
            throw new WP_ErrorException($terms);
        }

        return $terms;
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
        $this->query->put($parameter, $value);

        return $this;
    }
}
