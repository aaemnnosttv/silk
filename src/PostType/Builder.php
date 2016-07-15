<?php

namespace Silk\PostType;

use Silk\Type\Builder as BaseBuilder;
use Silk\PostType\Exception\InvalidPostTypeNameException;

class Builder extends BaseBuilder
{
    /**
     * Default PostType labels
     * @var array
     */
    protected $labelDefaults = [
        'add_new_item'          => 'Add New {one}',
        'all_items'             => 'All {many}',
        'archives'              => '{one} Archives',
        'edit_item'             => 'Edit {one}',
        'filter_items_list'     => 'Filter {many} list',
        'insert_into_item'      => 'Insert into {one}',
        'items_list_navigation' => '{many} list navigation',
        'items_list'            => '{many} list',
        'menu_name'             => '{many}',
        'name_admin_bar'        => '{one}',
        'name'                  => '{many}',
        'new_item'              => 'New {one}',
        'not_found_in_trash'    => 'No {many} found in Trash.',
        'not_found'             => 'No {many} found.',
        'search_items'          => 'Search {many}',
        'singular_name'         => '{one}',
        'uploaded_to_this_item' => 'Uploaded to this {one}',
        'view_item'             => 'View {one}',
    ];

    /**
     * Specify which features the post type supports.
     *
     * @param  mixed $features  array of features
     *         string ...$features  features as parameters
     *
     * @return $this
     */
    public function supports($features)
    {
        if (! is_array($features)) {
            $features = func_get_args();
        }

        return $this->set('supports', $features);
    }

    /**
     * Set the post type as publicly available.
     *
     * @return $this
     */
    public function open()
    {
        return $this->set('public', true);
    }

    /**
     * Set the post type as non-publicly available.
     *
     * @return $this
     */
    public function closed()
    {
        return $this->set('public', false);
    }

    /**
     * Enable admin interface.
     *
     * @return $this
     */
    public function withUI()
    {
        return $this->set('show_ui', true);
    }

    /**
     * Disable admin interface.
     *
     * @return $this
     */
    public function noUI()
    {
        return $this->set('show_ui', false);
    }

    /**
     * Register the post type.
     *
     * @return PostType
     */
    public function register()
    {
        if (! $this->id || strlen($this->id) > 20) {
            throw new InvalidPostTypeNameException('Post type names must be between 1 and 20 characters in length.');
        }

        $object = register_post_type($this->id, $this->assembleArgs());

        return new PostType($object);
    }
}
