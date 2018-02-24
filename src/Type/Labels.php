<?php

namespace Silk\Type;

use Silk\Support\Collection;

class Labels extends Collection
{
    /**
     * Label form for a single entity
     * @var string
     */
    protected $singularForm;

    /**
     * Label form for a multiple entities
     * @var string
     */
    protected $pluralForm;

    /**
     * Set the singular labels using the given form.
     *
     * @param string $label The singular label form to use
     *
     * @return $this
     */
    public function setSingular($label)
    {
        $this->singularForm = $label;

        return $this;
    }

    /**
     * Set the plural labels using the given form.
     *
     * @param string $label The plural label form to use
     *
     * @return $this
     */
    public function setPlural($label)
    {
        $this->pluralForm = $label;

        return $this;
    }

    /**
     * Get all the labels as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->map(function ($label) {
            return str_replace(
                [
                    '{one}',
                    '{many}'
                ],
                [
                    $this->singularForm,
                    $this->pluralForm
                ],
                $label
            );
        })->all();
    }
}
