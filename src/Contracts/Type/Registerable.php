<?php

namespace Silk\Contracts\Type;

interface Registerable
{
    /**
     * Get the unique identifier for the type.
     *
     * @return string
     */
    public function id();

    /**
     * Load an existing type instance.
     *
     * @param $id
     *
     * @return mixed
     */
    public static function load($id);

    /**
     * Build a new type to be registered.
     *
     * @param $id
     *
     * @return mixed
     */
    public static function build($id);

    /**
     * Check to see if a type exists with the given identifier.
     *
     * @param $id
     *
     * @return bool
     */
    public static function exists($id);
}
