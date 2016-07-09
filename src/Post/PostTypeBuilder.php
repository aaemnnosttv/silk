<?php

namespace Silk\Post;

use Silk\Post\PostTypeLabels;
use Illuminate\Support\Collection;
use Silk\Post\Exception\InvalidPostTypeNameException;

/**
 * @property-read string $slug
 * @property-read string $one
 * @property-read string $many
 */
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
     * @var PostTypeLabels
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
        $registered_labels = $this->args->get('labels', []);

        /**
         * Override any generated labels with those that were passed in arguments.
         */
        $labels = array_merge($this->labels()->toArray(), $registered_labels);

        return $this->args->put('labels', $labels)->toArray();
    }

    /**
     * Get the labels instance.
     *
     * @return PostTypeLabels
     */
    protected function labels()
    {
        if (! $this->labels) {
            $this->labels = new PostTypeLabels;
        }

        return $this->labels;
    }

    /**
     * Magic Getter
     *
     * @param  string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        switch ($property) :
            case 'slug':
                return $this->slug;
            case 'one':
                return $this->labelSingular;
            case 'many':
                return $this->labelPlural;
        endswitch;

        return null;
    }
}
