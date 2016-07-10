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
     * The master collection of labels
     * @var Collection
     */
    protected $labels;

    /**
     * Set the singular labels using the given form.
     *
     * @param $label The singular label form to use
     *
     * @return $this
     */
    public function setSingular($label)
    {
        $this->merge(
            LabelsCollection::make($this->singular)->setForm($label)
        );

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
        $this->merge(
            LabelsCollection::make($this->plural)->setForm($label)
        );

        return $this;
    }

    /**
     * Get all the labels as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->labels ? $this->labels->toArray() : [];
    }

    /**
     * Merge the labels with the master collection.
     *
     * @param  LabelsCollection $collection
     */
    protected function merge(LabelsCollection $collection)
    {
        if (! $this->labels) {
            $this->labels = new Collection;
        }

        $this->labels = $this->labels->merge($collection->replaced());
    }
}
