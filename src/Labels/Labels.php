<?php

namespace Silk\Labels;

use Silk\Labels\LabelsCollection;
use Illuminate\Support\Collection;

class Labels
{
    /**
     * Labels referencing the singular form
     * @var array
     */
    protected $singular = [];

    /**
     * Labels referencing the plural form
     * @var array
     */
    protected $plural = [];

    /**
     * [$extra description]
     * @var [type]
     */
    protected $extra = [];

    /**
     * The master collection of labels
     * @var Collection
     */
    protected $collection;

    /**
     * Set the singular labels using the given form.
     *
     * @param $label The singular label form to use
     *
     * @return $this
     */
    public function setSingular($label)
    {
        $this->collect()->get('singular')->setForm($label);

        return $this;
    }

    /**
     * Set the plural labels using the given form.
     *
     * @param $label The plural label form to use
     *
     * @return $this
     */
    public function setPlural($label)
    {
        $this->collect()->get('plural')->setForm($label);

        return $this;
    }

    /**
     * Set the label in the appropriate collection.
     *
     * @param string $key   Label key
     * @param string $label Label value
     */
    public function set($key, $label)
    {
        $this->collect()
            ->first(function ($index, Collection $collection) use ($key) {
                return $collection->has($key);
            }, $this->collect()->get('extra'))
            ->put($key, $label);

        return $this;
    }

    /**
     * Get all the labels as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->collect()->map(function (LabelsCollection $collection) {
            return $collection->replaced();
        })->collapse()->toArray();
    }

    /**
     * Get the master collection.
     *
     * @return Collection
     */
    public function collect()
    {
        if (! $this->collection) {
            $this->collection = Collection::make([
                'singular',
                'plural',
                'extra'
            ])->flip()->map(function ($value, $key) {
                return LabelsCollection::make($this->$key);
            });
        }

        return $this->collection;
    }
}
