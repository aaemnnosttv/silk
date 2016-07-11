<?php

namespace Silk\Taxonomy;

use Silk\Post\PostType;
use Silk\Taxonomy\Builder;
use Silk\Term\TermQueryBuilder;
use Illuminate\Support\Collection;
use Silk\Exception\WP_ErrorException;

/**
 * @property-read string   $id
 * @property-read stdClass $taxonomy
 *
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
class Taxonomy
{
    /**
     * The Taxonomy identifier
     * @var string
     */
    protected $id;

    /**
     * The taxonomy object
     * @var object
     */
    protected $taxonomy;

    /**
     * Taxonomy Constructor.
     *
     * @param object $taxonomy The taxonomy object
     *
     * @throws Exception\NonExistentTaxonomyException
     */
    public function __construct($taxonomy)
    {
        if (empty($taxonomy->name) || ! static::exists($taxonomy->name)) {
            throw new Exception\NonExistentTaxonomyException;
        }

        $this->id = $taxonomy->name;
        $this->taxonomy = $taxonomy;
    }

    /**
     * Create a new instance using the taxonomy identifier.
     *
     * @param  string $identifier Taxonomy name/identifier
     *
     * @return static
     */
    public static function make($identifier)
    {
        if (static::exists($identifier)) {
            return new static(get_taxonomy($identifier));
        }

        return new Builder($identifier);
    }

    /**
     * Check if the given taxonomy exists.
     *
     * @param  string $identifier The taxonomy identifier
     *
     * @return bool
     */
    public static function exists($identifier)
    {
        return taxonomy_exists($identifier);
    }

    /**
     * Start a new query for terms of this taxonomy.
     *
     * @return TermQueryBuilder
     */
    public function terms()
    {
        return (new TermQueryBuilder)->forTaxonomy($this->id);
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
     * @throws WP_ErrorException
     * @throws Exception\NonExistentTaxonomyException
     *
     * @return $this
     */
    public function unregister()
    {
        if (! static::exists($this->taxonomy->name)) {
            throw new Exception\NonExistentTaxonomyException;
        }

        if (is_wp_error($error = unregister_taxonomy($this->taxonomy->name))) {
            throw new WP_ErrorException($error);
        }

        return $this;
    }

    /**
     * Magic Getter.
     *
     * @param  string $property Accessed property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }

        if (isset($this->taxonomy->$property)) {
            return $this->taxonomy->$property;
        }

        return null;
    }
}
