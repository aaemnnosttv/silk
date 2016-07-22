<?php

namespace Silk\Term;

use stdClass;
use WP_Term;
use Silk\Taxonomy\Taxonomy;
use Silk\Type\Model as BaseModel;
use Illuminate\Support\Collection;
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
     * @param mixed $term  WP_Term to fill data from
     *
     * @throws TaxonomyMismatchException
     */
    public function __construct(WP_Term $term = null)
    {
        if (! $term) {
            $term = new WP_Term(new stdClass);
            $term->taxonomy = static::TAXONOMY;
        } elseif ($term->taxonomy != static::TAXONOMY) {
            throw new TaxonomyMismatchException();
        }

        $this->object = $term;
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
     * @throws TermNotFoundException
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
     * @throws TermNotFoundException
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
                ->except([static::ID_PROPERTY, 'term_taxonomy_id'])
                ->put('taxonomy', static::TAXONOMY)
                ->toArray()
        )->save();
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
            ->map(function ($term_ID) {
                return static::fromID($term_ID);
            });
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
     * Start a new query for terms of this type.
     *
     * @return QueryBuilder
     */
    public function newQuery()
    {
        return (new QueryBuilder)->setModel($this);
    }

    /**
     * Get the array of actions and their respective handler classes.
     *
     * @return array
     */
    protected function actionClasses()
    {
        return [
            'save'   => Action\TermSaver::class,
            'load'   => Action\TermLoader::class,
            'delete' => Action\TermDeleter::class,
        ];
    }
}
