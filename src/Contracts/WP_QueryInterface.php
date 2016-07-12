<?php

namespace Silk\Contracts;

interface WP_QueryInterface
{
    /**
     * Set query variable.
     *
     * @param string $key   Query variable key.
     * @param string $value Query variable value.
     */
    public function set($key, $value);

    /**
     * Retrieve query variable.
     *
     * @param  string $key     Query variable key.
     * @param  string $default Value to return if the query variable is not set.
     *
     * @return mixed
     */
    public function get($key, $default = '');

    /**
     * Retrieve the posts based on query variables.
     *
     * @return array
     */
    public function get_posts();
}
