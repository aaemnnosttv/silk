<?php

use Silk\Hook;

if (! function_exists('on')) :
    /**
     * Create and set a new event listener
     *
     * @param  string   $handle    action or filter handle
     * @param  callable $callback
     * @return Hook
     */
    function on($handle, callable $callback)
    {
        return Hook::on($handle)
            ->setCallback($callback)
            ->listen();
    }
endif;

if (! function_exists('off')) :
    /**
     * Remove an event listener
     *
     * Will attempt to remove on-demand as a fallback
     *
     * @param  [type] $handle   [description]
     * @param  [type] $callback [description]
     * @return bool|Hook        true if immediately removed, Hook instance if not
     */
    function off($handle, $callback, $priority = 10)
    {
        if ($removed = remove_filter($handle, $callback, $priority)) {
            return $removed;
        }

        return on($handle, function ($given = null) use ($handle, $callback, $priority) {
            remove_filter($handle, $callback, $priority);
            return $given;
        })->withPriority($priority - 1);
    }
endif;
