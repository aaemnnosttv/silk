<?php

namespace Silk\Taxonomy;

use Silk\Taxonomy\Taxonomy;
use Silk\Type\Builder as TypeBuilder;
use Silk\Exception\WP_ErrorException;
use Illuminate\Support\Collection;

class Builder extends TypeBuilder
{
    /**
     * Object types this taxonomy will be registered for
     * @var array
     */
    protected $objectTypes = [];

    /**
     * Default taxonomy labels
     * @var array
     */
    protected $labelDefaults = [
        'add_new_item'               => 'Add New {one}',
        'add_or_remove_items'        => NULL,
        'all_items'                  => 'All {many}',
        'archives'                   => 'All {many}',
        'choose_from_most_used'      => NULL,
        'edit_item'                  => 'Edit {one}',
        'items_list_navigation'      => '{many} list navigation',
        'items_list'                 => '{many} list',
        'menu_name'                  => '{many}',
        'name_admin_bar'             => '{one}',
        'name'                       => '{many}',
        'new_item_name'              => 'New {one} Name',
        'no_terms'                   => 'No {many}',
        'not_found'                  => 'No {many} found.',
        'parent_item_colon'          => 'Parent {one}:',
        'parent_item'                => 'Parent {one}',
        'popular_items'              => NULL,
        'search_items'               => 'Search {many}',
        'separate_items_with_commas' => NULL,
        'singular_name'              => '{one}',
        'update_item'                => 'Update {one}',
        'view_item'                  => 'View {one}',
    ];

    /**
     * Specify which object types the taxonomy is for.
     *
     * @param  ...string|array $types  A list of object types or an array.
     *
     * @return $this
     */
    public function forTypes($types)
    {
        $this->objectTypes = is_array($types) ? $types : func_get_args();

        return $this;
    }

    /**
     * Register and return the new taxonomy.
     *
     * @return Taxonomy
     */
    public function register()
    {
        $error = register_taxonomy($this->id, $this->objectTypes, $this->assembleArgs());

        if (is_wp_error($error)) {
            throw new WP_ErrorException($error);
        }

        return Taxonomy::make($this->id);
    }
}
