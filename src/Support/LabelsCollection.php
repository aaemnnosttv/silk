<?php

namespace Silk\Support;

use Illuminate\Support\Collection;

class LabelsCollection extends Collection
{
    /**
     * The label form (singular, plural, or otherwise)
     * @var string
     */
    protected $form;

    /**
     * Set the form.
     *
     * @param string $label  The label form to use for this collection
     */
    public function setForm($label)
    {
        $this->form = $label;

        return $this;
    }

    /**
     * Get a new collection with replaced placeholders in items using the provided form.
     *
     * @return $this
     */
    public function replaced()
    {
        return $this->map(function ($label) {
            return $this->replaceWithForm($label);
        });
    }

    /**
     * Replace all placeholders in the label with the given label form.
     *
     * @param  string $label The label to make replacements in
     *
     * @return string   The label after replacements have been made
     */
    protected function replaceWithForm($label)
    {
        return sprintf($label, $this->form);
    }

    /**
     * Get a specific label, after replacements have been made.
     *
     * @param  string $key     The label key
     * @param  string $default The default value, if no label if found for key
     *
     * @return string The label after replacements have been made
     */
    public function get($key, $default = null)
    {
        $label = parent::get($key, $default);

        return $this->replaceWithForm($label);
    }

    /**
     * Make replacements and return the collection as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->replaced()->all();
    }
}
