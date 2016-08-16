<?php

namespace Silk\Type;

interface Registerable
{
    /**
     * Load an existing type instance.
     *
     * @param $id
     *
     * @return mixed
     */
    static function load($id);

    /**
     * Build a new type to be registered.
     *
     * @param $id
     *
     * @return mixed
     */
    static function build($id);

    /**
     * Check to see if a type exists with the given identifier.
     *
     * @param $id
     *
     * @return mixed
     */
    static function exists($id);
}
