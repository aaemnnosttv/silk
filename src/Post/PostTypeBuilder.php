<?php

namespace Silk\Post;

use Silk\Support\Labels;
use Illuminate\Support\Collection;
use Silk\Post\Exception\InvalidPostTypeNameException;

class PostTypeBuilder
{
    /**
     * The post type slug.
     * @var string
     */
    protected $slug;

    /**
     * The arguments to be passed when registering the post type.
     * @var Collection
     */
    protected $args;

    /**
     * The Post Type Labels
     * @var Labels
     */
    protected $labels;

    /**
     * PostTypeBuilder constructor
     *
     * @param string $slug  post type slug
     * @param array $args   initial registration arguments
     */
    public function __construct($slug, array $args = [])
    {
        if (strlen($slug) < 1 || strlen($slug) > 20) {
            throw new InvalidPostTypeNameException('Post type names must be between 1 and 20 characters in length.');
        }

        $this->slug = $slug;
        $this->args = new Collection($args);
    }

    /**
     * Create a new instance
     *
     * @param  string $slug
     *
     * @return static
     */
    public static function make($slug)
    {
        return new static($slug);
    }

    /**
     * Specify which features the post type supports
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
     * Set the post type as publicly available
     *
     * @return $this
     */
    public function open()
    {
        return $this->set('public', true);
    }

    /**
     * Set the post type as non-publicly available
     *
     * @return $this
     */
    public function closed()
    {
        return $this->set('public', false);
    }

    /**
     * Enable admin interface
     *
     * @return $this
     */
    public function withUI()
    {
        return $this->set('show_ui', true);
    }

    /**
     * Disable admin interface
     *
     * @return $this
     */
    public function noUI()
    {
        return $this->set('show_ui', false);
    }

    /**
     * Set the singular label for this post type
     *
     * @param  string $singular_label
     *
     * @return $this
     */
    public function oneIs($singular_label)
    {
        $this->labels()->setSingular($singular_label);

        return $this;
    }

    /**
     * Set the plural label for this post type
     *
     * @param  string $plural_label
     *
     * @return $this
     */
    public function manyAre($plural_label)
    {
        $this->labels()->setPlural($plural_label);

        return $this;
    }

    /**
     * Setter for post type arguments
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->args->put($key, $value);

        return $this;
    }

    /**
     * Set a label for the given key.
     *
     * @param string $key   Label key
     * @param string $value Label value
     *
     * @return $this
     */
    public function setLabel($key, $value)
    {
        $this->labels()->put($key, $value);

        return $this;
    }

    /**
     * Getter for post type arguments
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->args->get($key);
    }

    /**
     * Register the post type
     *
     * @return PostType
     */
    public function register()
    {
        $object = register_post_type($this->slug, $this->assembleArgs());

        return new PostType($object);
    }

    /**
     * Assemble the arguments for post type registration.
     *
     * @return array
     */
    protected function assembleArgs()
    {
        return $this->args->put('labels', $this->labels())->toArray();
    }

    /**
     * Get the labels instance.
     *
     * @return PostTypeLabels
     */
    protected function labels()
    {
        if (! $this->labels) {
            $this->labels = Labels::make([
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
            ])->merge($this->args->get('labels', []));
        }

        return $this->labels;
    }
}
