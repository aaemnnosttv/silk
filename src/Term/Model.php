<?php

namespace Silk\Term;

use stdClass;
use WP_Term;
use Silk\Meta\TypeMeta;
use Silk\Taxonomy\Taxonomy;
use Silk\Query\QueryBuilder;
use Illuminate\Support\Collection;
use Silk\Exception\WP_ErrorException;
use Silk\Term\Exception\TermNotFoundException;
use Silk\Term\Exception\TaxonomyMismatchException;

/**
 * @property-read int $id
 * 
 * @property int    $term_id
 * @property string $name
 * @property string $slug
 * @property string $term_group
 * @property int    $term_taxonomy_id
 * @property string $taxonomy
 * @property string $description
 * @property int    $parent
 * @property int    $count
 */
abstract class Model
{
    /**
     * The term's taxonomy
     * @var string
     */
    const TAXONOMY = '';

    /**
     * The object type in WordPress
     * @var string
     */
    const OBJECT_TYPE = 'term';

    /**
     * The term object
     * @var WP_Term
     */
    protected $term;

    use TypeMeta;
    use QueryBuilder;

    /**
     * Model Constructor.
     *
     * @param mixed $term  WP_Term to fill data from
     */
    public function __construct(WP_Term $term = null)
    {
        if (! $term) {
            $term = new WP_Term(new stdClass);
            $term->taxonomy = static::TAXONOMY;
        } elseif ($term->taxonomy != static::TAXONOMY) {
            throw new TaxonomyMismatchException();
        }

        $this->term = $term;
    }

    /**
     * Create a new instance from a WP_Term object.
     *
     * @param  WP_Term $term [description]
     *
     * @return static
     */
    public static function fromWpTerm(WP_Term $term)
    {
        return new static($term);
    }

    /**
     * Create a new instance from a term ID.
     *
     * @param  int|string $id  Term ID
     *
     * @return static
     */
    public static function fromID($id)
    {
        if (! $term = get_term_by('id', (int) $id, static::TAXONOMY)) {
            throw new TermNotFoundException("No term found with ID $id.");
        }

        return static::fromWpTerm($term);
    }

    /**
     * Create a new instance from a slug.
     *
     * @param  string $slug  Term slug
     *
     * @return static
     */
    public static function fromSlug($slug)
    {
        if (! $term = get_term_by('slug', $slug, static::TAXONOMY)) {
            throw new TermNotFoundException("No term found with slug '$slug'.");
        }

        return static::fromWpTerm($term);
    }

    /**
     * Create a new instance from an array of attributes.
     *
     * @param  array  $attributes [description]
     *
     * @return static
     */
    public static function fromArray(array $attributes)
    {
        return new static(
            new WP_Term((object) $attributes)
        );
    }

    /**
     * Create a new term, and get the instance for it.
     *
     * @param  array $attributes  Term attributes
     *
     * @return static
     */
    public static function create(array $attributes = [])
    {
        return static::fromArray(
            Collection::make($attributes)
                ->except(['term_id', 'term_taxonomy_id'])
                ->put('taxonomy', static::TAXONOMY)
                ->toArray()
        )->save();
    }

    /**
     * Save or update the term instance in the database.
     *
     * @return $this
     */
    public function save()
    {
        if ($this->exists()) {
            $ids = wp_update_term($this->id, static::TAXONOMY, $this->term->to_array());
        } else {
            $ids = wp_insert_term($this->name, static::TAXONOMY, $this->term->to_array());
        }

        if (is_wp_error($ids)) {
            throw new WP_ErrorException($ids);
        }

        foreach ($ids as $field => $id) {
            $this->term->$field = $id;
        }

        return $this;
    }

    /**
     * Delete the term from the database.
     *
     * @return $this
     */
    public function delete()
    {
        if ($result = wp_delete_term($this->id, static::TAXONOMY)) {
            $this->term->term_id = null;
            $this->term->term_taxonomy_id = 0;
        }

        return $this;
    }

    /**
     * Check if this term exists in the database.
     *
     * @return boolean
     */
    public function exists()
    {
        return (bool) term_exists((int) $this->id, static::TAXONOMY);
    }

    /**
     * Check if this term exists in the database as the child of the given parent.
     *
     * @param  int|string|object  $parent  integer Parent term ID
     *                                     string  Parent term slug or name
     *                                     object  The parent term object/model.
     *
     * @return boolean                     True if the this term and the parent
     *                                     exist in the database, and the instance
     *                                     is a child of the given parent;
     *                                     otherwise false
     */
    public function isChildOf($parent)
    {
        if (isset($parent->term_id)) {
            $parent = $parent->term_id;
        }

        return (bool) term_exists((int) $this->id, static::TAXONOMY, $parent);
    }

    /**
     * Get the parent term instance.
     *
     * @return static
     */
    public function parent()
    {
        return static::fromID($this->term->parent);
    }

    /**
     * Get all ancestors of this term as a collection.
     *
     * @return Collection
     */
    public function ancestors()
    {
        return Collection::make(get_ancestors($this->id, static::TAXONOMY, 'taxonomy'))
            ->map(function ($term_ID) {
                return static::fromID($term_ID);
            });
    }

    /**
     * Get the Taxonomy model.
     *
     * @return Taxonomy
     */
    public function taxonomy()
    {
        return Taxonomy::make($this->taxonomy);
    }

    /**
     * Start a new query for terms of this type.
     *
     * @return TermQueryBuilder
     */
    public function newQuery()
    {
        return (new TermQueryBuilder)->setModel($this);
    }

    /**
     * Magic Getter.
     *
     * @param  string $property Property name accessed
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ('id' == strtolower($property)) {
            return $this->term->term_id;
        }

        if (isset($this->term->$property)) {
            return $this->term->$property;
        }

        return null;
    }

    /**
     * Magic set checker.
     *
     * @param  string  $property  Property name queried
     *
     * @return boolean
     */
    public function __isset($property)
    {
        return property_exists($this->term, $property);
    }

    /**
     * Magic Setter.
     *
     * @param string $property  Property name assigned
     * @param mixed  $value     Assigned property value
     */
    public function __set($property, $value)
    {
        $this->term->$property = $value;
    }
}
