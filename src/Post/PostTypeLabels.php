<?php

namespace Silk\Post;

use Silk\Labels\Labels;

class PostTypeLabels extends Labels
{
    /**
     * Labels referencing the singular form
     * @var array
     */
    protected $singular = [
        'add_new_item'          => 'Add New %s',
        'archives'              => '%s Archives',
        'edit_item'             => 'Edit %s',
        'insert_into_item'      => 'Insert into %s',
        'name_admin_bar'        => '%s',
        'new_item'              => 'New %s',
        'singular_name'         => '%s',
        'uploaded_to_this_item' => 'Uploaded to this %s',
        'view_item'             => 'View %s',
    ];

    /**
     * Labels referencing the plural form
     * @var array
     */
    protected $plural = [
        'all_items'             => 'All %s',
        'filter_items_list'     => 'Filter %s list',
        'items_list_navigation' => '%s list navigation',
        'items_list'            => '%s list',
        'menu_name'             => '%s',
        'name'                  => '%s',
        'not_found_in_trash'    => 'No %s found in Trash.',
        'not_found'             => 'No %s found.',
        'search_items'          => 'Search %s',
    ];
}
