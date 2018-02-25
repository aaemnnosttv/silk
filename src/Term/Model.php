<?php

namespace Silk\Term;

use stdClass;
use WP_Term;
use Silk\Taxonomy\Taxonomy;
use Silk\Type\Model as BaseModel;
use Silk\Support\Collection;
use Silk\Exception\WP_ErrorException;
use Silk\Term\Exception\TermNotFoundException;
use Silk\Term\Exception\TaxonomyMismatchException;

/**
 * @property-read WP_Term $term
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
abstract class Model extends BaseModel
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
     * The primary ID property on the object
     */
    const ID_PROPERTY = 'term_id';

    /**
     * Model Constructor.
     *
     * @param array|WP_Term $term  WP_Term
     *
     * @throws TaxonomyMismatchException
     */
    public function __construct($term = [])
    {
        $attributes = is_array($term) ? $term : [];

        if (! $term instanceof WP_Term) {
            $term = new WP_Term(new stdClass);
            $term->taxonomy = static::TAXONOMY;
        } elseif ($term->taxonomy != static::TAXONOMY) {
            throw new TaxonomyMismatchException();
        }

        $this->setObject($term);

        $this->fill($attributes);
    }

    /**
     * Retrieve a new instance by the ID.
     *
     * @param int|string $id Primary ID
     *
     * @return null|static
     */
    public static function find($id)
    {
        try {
            return static::fromID($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a new instance from a term ID.
     *
     * @param  int|string $id  Term ID
     *
     * @throws TermNotFoundException
     *
     * @return static
     */
    public static function fromID($id)
    {
        if (! $term = WP_Term::get_instance($id, static::TAXONOMY)) {
            throw new TermNotFoundException("No term found with ID $id.");
        }

        return new static($term);
    }

    /**
     * Create a new instance from a slug.
     *
     * @param  string $slug  Term slug
     *
     * @throws TermNotFoundException
     *
     * @return static
     */
    public static function fromSlug($slug)
    {
        if (! $term = get_term_by('slug', $slug, static::TAXONOMY)) {
            throw new TermNotFoundException("No term found with slug '$slug'.");
        }

        return new static($term);
    }

    /**
     * Check if this term exists in the database.
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->id && ((bool) term_exists((int) $this->id, static::TAXONOMY));
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
        return static::fromID($this->object->parent);
    }

    /**
     * Get all ancestors of this term as a collection.
     *
     * @return Collection
     */
    public function ancestors()
    {
        return Collection::make(get_ancestors($this->id, static::TAXONOMY, 'taxonomy'))
            ->map([static::class, 'fromID']);
    }

    /**
     * Get all children of this term as a collection.
     *
     * @return Collection
     */
    public function children()
    {
        return Collection::make(get_term_children($this->id, static::TAXONOMY))
             ->map([static::class, 'fromID']);
    }

    /**
     * Get the Taxonomy model.
     *
     * @return Taxonomy|\Silk\Taxonomy\Builder
     */
    public static function taxonomy()
    {
        return Taxonomy::make(static::TAXONOMY);
    }

    /**
     * Get the URL for this term.
     *
     * @return string|bool
     */
    public function url()
    {
        $url = get_term_link($this->id, $this->taxonomy);

        if (is_wp_error($url)) {
            throw new WP_ErrorException($url);
        }

        return $url;
    }

    /**
     * Start a new query for terms of this type.
     *
     * @return QueryBuilder
     */
    public function newQuery()
    {
        return QueryBuilder::make()->setModel($this);
    }

    /**
     * Save the term to the database.
     *
     * @throws WP_ErrorException
     *
     * @return $this
     */
    public function save()
    {
        if ($this->id) {
            $ids = wp_update_term($this->id, $this->taxonomy, $this->object->to_array());
        } else {
            $ids = wp_insert_term($this->name, $this->taxonomy, $this->object->to_array());
        }

        if (is_wp_error($ids)) {
            throw new WP_ErrorException($ids);
        }

        $this->setId($ids['term_id'])->refresh();

        return $this;
    }

    /**
     * Delete the term from the database.
     *
     * @return $this
     */
    public function delete()
    {
        if (wp_delete_term($this->id, $this->taxonomy)) {
            $this->setObject(new WP_Term(new stdClass));
        }

        return $this;
    }

    /**
     * Reload the term object from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->setObject(WP_Term::get_instance($this->id, $this->taxonomy));

        return $this;
    }
}
