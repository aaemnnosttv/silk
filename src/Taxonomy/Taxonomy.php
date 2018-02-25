<?php

namespace Silk\Taxonomy;

use stdClass;
use Silk\Type\Type;
use Silk\Contracts\Type\Registerable;
use Silk\PostType\PostType;
use Silk\Term\QueryBuilder;
use Silk\Support\Collection;
use Silk\Exception\WP_ErrorException;
use Silk\Taxonomy\Exception\InvalidTaxonomyNameException;
use Silk\Taxonomy\Exception\NonExistentTaxonomyException;

/**
 * @property-read bool     $_builtin
 * @property-read stdClass $cap
 * @property-read string   $description
 * @property-read bool     $hierarchical
 * @property-read string   $label
 * @property-read stdClass $labels
 * @property-read callable $meta_box_cb
 * @property-read string   $name
 * @property-read array    $object_type
 * @property-read bool     $public
 * @property-read bool     $publicly_queryable
 * @property-read string   $query_var
 * @property-read array    $rewrite
 * @property-read bool     $show_admin_column
 * @property-read bool     $show_in_menu
 * @property-read bool     $show_in_nav_menus
 * @property-read bool     $show_in_quick_edit
 * @property-read bool     $show_tagcloud
 * @property-read bool     $show_ui
 * @property-read callable $update_count_callback
 */
class Taxonomy extends Type implements Registerable
{
    /**
     * Taxonomy Constructor.
     *
     * @param object $taxonomy The taxonomy object
     *
     * @throws NonExistentTaxonomyException
     */
    public function __construct($taxonomy)
    {
        if (empty($taxonomy->name) || ! static::exists($taxonomy->name)) {
            throw new NonExistentTaxonomyException;
        }

        $this->object = $taxonomy;
    }

    /**
     * Create a new instance using the taxonomy identifier.
     *
     * @param  string $id Taxonomy name/identifier
     *
     * @throws NonExistentTaxonomyException
     * @throws InvalidTaxonomyNameException
     *
     * @return static|Builder
     */
    public static function make($id)
    {
        if (static::exists($id)) {
            return static::load($id);
        }

        if (! $id || strlen($id) > 32) {
            throw new InvalidTaxonomyNameException('Taxonomy names must be between 1 and 32 characters in length.');
        }

        return static::build($id);
    }

    /**
     * Create a new instance from an existing taxonomy.
     *
     * @param  string $id The taxonomy identifier
     *
     * @throws NonExistentTaxonomyException
     *
     * @return static
     */
    public static function load($id)
    {
        if (! $object = get_taxonomy($id)) {
            throw new NonExistentTaxonomyException("No taxonomy exists with name '$id'.");
        }

        return new static($object);
    }

    /**
     * Build a new Taxonomy to be registered.
     *
     * @param $id
     *
     * @return Builder
     */
    public static function build($id)
    {
        return new Builder($id);
    }

    /**
     * Check if a Taxonomy exists for the given identifier.
     *
     * @param  string $id The taxonomy key/identifier
     *
     * @return bool
     */
    public static function exists($id)
    {
        return taxonomy_exists($id);
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->object->name;
    }

    /**
     * Start a new query for terms of this taxonomy.
     *
     * @return QueryBuilder
     */
    public function terms()
    {
        return (new QueryBuilder)->forTaxonomy($this->id());
    }

    /**
     * Get all post types associated with this taxonomy.
     *
     * @return Collection
     */
    public function postTypes()
    {
        return Collection::make($this->object_type)
            ->map(function ($post_type) {
                return PostType::load($post_type);
            });
    }

    /**
     * Unregister the taxonomy.
     *
     * @throws NonExistentTaxonomyException
     * @throws WP_ErrorException
     *
     * @return $this
     */
    public function unregister()
    {
        if (! $this->exists($this->id())) {
            throw new NonExistentTaxonomyException;
        }

        if (is_wp_error($error = unregister_taxonomy($this->id()))) {
            throw new WP_ErrorException($error);
        }

        return $this;
    }
}
