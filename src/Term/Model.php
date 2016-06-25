<?php

namespace Silk\Term;

use stdClass;
use WP_Term;
use Silk\Exception\WP_ErrorException;
use Silk\Term\Exception\TermNotFoundException;
use Silk\Term\Exception\TaxonomyMismatchException;

/**
 * @property-read int $id
 * @property int $term_id
 * @property string $name
 * @property string $slug
 * @property string $term_group
 * @property int $term_taxonomy_id
 * @property string $taxonomy
 * @property string $description
 * @property int $parent
 * @property int $count
 */
abstract class Model
{
    /**
     * The term's taxonomy
     * @var string
     */
    const TAXONOMY = '';

    /**
     * The term object
     * @var WP_Term
     */
    protected $term;

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
        }

        if ($term->taxonomy !== static::TAXONOMY) {
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
     * Save or update the term instance in the database.
     *
     * @return $this
     */
    public function save()
    {
        $ids = wp_insert_term($this->name, static::TAXONOMY);

        if (is_wp_error($ids)) {
            throw new WP_ErrorException($ids);
        }

        foreach ($ids as $field => $id) {
            $this->term->$field = $id;
        }

        return $this;
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
