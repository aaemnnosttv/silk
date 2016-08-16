<?php

namespace Silk\Type;

trait ObjectAliases
{
    /**
     * Get all object aliases as a dictionary.
     *
     * Eg. ['aliasName' => 'propertyNameOnObject']
     *
     * @return array
     */
    abstract protected function objectAliases();

    /**
     * Get the aliased object instance.
     *
     * @return object
     */
    abstract protected function getAliasedObject();

    /**
     * Get a property from the aliased object by the model's key.
     *
     * @param $key
     *
     * @return mixed|null
     */
    protected function aliasGet($key)
    {
        if (! $expanded = $this->expandAlias($key)) {
            return null;
        }

        return data_get($this->getAliasedObject(), $expanded);
    }

    /**
     * Set a property on the aliased object.
     *
     * @param string $key   The alias name on the model
     * @param mixed  $value The value to set on the aliased object
     *
     * @return bool          True if the alias was resolved and set; otherwise false
     */
    protected function aliasSet($key, $value)
    {
        $expanded = $this->expandAlias($key);

        if ($expanded && is_object($aliased = $this->getAliasedObject())) {
            $aliased->$expanded = $value;
            return true;
        }

        return false;
    }

    /**
     * Expands an alias into its respective object property name.
     *
     * @param string $key  Alias key
     *
     * @return string|false  The expanded alias, or false no alias exists for the key.
     */
    protected function expandAlias($key)
    {
        return data_get($this->objectAliases(), $key, false);
    }
}
