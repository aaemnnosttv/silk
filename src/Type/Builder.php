<?php

namespace Silk\Type;

use Illuminate\Support\Collection;

abstract class Builder
{
    /**
     * The type identifier
     * @var string
     */
    protected $id;

    /**
     * Registration arguments
     * @var Collection
     */
    protected $args;

    /**
     * Labels collection
     * @var Labels
     */
    protected $labels;

    /**
     * Default labels
     * @var array
     */
    protected $labelDefaults = [];

    /**
     * Builder Constructor.
     *
     * @param string $id   [description]
     * @param array  $args [description]
     */
    public function __construct($id, array $args = [])
    {
        $this->id = $id;
        $this->args = new Collection($args);
    }

    /**
     * Create a new instance.
     *
     * @param  string $type
     *
     * @return static
     */
    public static function make($type)
    {
        return new static($type);
    }

    /**
     * Register the type.
     *
     * @return mixed
     */
    abstract public function register();

    /**
     * Assemble the arguments for registration.
     *
     * @return array
     */
    protected function assembleArgs()
    {
        return $this->args->put('labels', $this->labels())->toArray();
    }

    /**
     * Set the singular label for this post type.
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
     * Set the plural label for this post type.
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
     * Get the labels instance.
     *
     * @return Labels
     */
    protected function labels()
    {
        if (! $this->labels) {
            $this->labels = Labels::make(
                $this->labelDefaults
            )->merge($this->args->get('labels', []));
        }

        return $this->labels;
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
     * Setter for post type arguments.
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
     * Getter for post type arguments.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->args->get($key);
    }
}
