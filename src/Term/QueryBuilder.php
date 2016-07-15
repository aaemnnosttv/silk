<?php

namespace Silk\Term;

use WP_Term;
use Silk\Contracts\BuildsQueries;
use Silk\Exception\WP_ErrorException;
use Illuminate\Support\Collection;

class QueryBuilder implements BuildsQueries
{
    /**
     * The term model
     * @var Model
     */
    protected $model;

    /**
     * Collection of arguments
     * @var Collection
     */
    protected $args;

    /**
     * Taxonomy Identifier
     * @var string
     */
    protected $taxonomy;

    /**
     * TermQueryBuilder Constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->args = Collection::make($args);
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
     * Include terms that have no related objects in the results.
     *
     * @return $this
     */
    public function includeEmpty()
    {
        $this->args->put('hide_empty', false);

        return $this;
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
        $this->args->put('number', intval($max_results));

        return $this;
    }

    /**
     * Get the query results.
     *
     * @throws WP_ErrorException
     *
     * @return Collection
     */
    public function results()
    {
        if ($this->model) {
            return $this->collectModels();
        }

        if ($this->taxonomy) {
            $this->args->put('taxonomy', $this->taxonomy);
        }

        return Collection::make($this->fetchTerms());
    }

    /**
     * Get the results as a collection of models.
     *
     * @return Collection
     */
    protected function collectModels()
    {
        $this->args->put('taxonomy', $this->model->taxonomy);
        $this->args->put('fields', 'all');

        $modelClass = get_class($this->model);

        return Collection::make($this->fetchTerms())
            ->map(function (WP_Term $term) use ($modelClass) {
                return new $modelClass($term);
            });
    }

    /**
     * Set the model for this query.
     *
     * @param mixed $model
     *
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the model.
     *
     * @return mixed Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Perform the term query and return the results.
     *
     * @throws WP_ErrorException
     *
     * @return array
     */
    protected function fetchTerms()
    {
        if (is_wp_error($terms = get_terms($this->args->toArray()))) {
            throw new WP_ErrorException($terms);
        }

        return $terms;
    }
}
