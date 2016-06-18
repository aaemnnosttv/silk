<?php

use Silk\Event\Hook;

if (! function_exists('on')) :
    /**
     * Create and set a new event listener.
     *
     * @param  string   $handle    action or filter handle
     * @param  callable $callback
     * @param  int      $priority
     *
     * @return Hook
     */
    function on($handle, callable $callback, $priority = 10)
    {
        return Hook::on($handle, $priority)
            ->setCallback($callback)
            ->listen();
    }
endif;

if (! function_exists('off')) :
    /**
     * Remove an event listener.
     *
     * If the callback cannot be removed immediately, attempt to remove it just-in-time as a fallback.
     *
     * @param  string $handle   action or filter handle
     * @param  callable $callback
     * @param  int $priority
     *
     * @return bool|Hook        true if immediately removed, Hook instance otherwise
     */
    function off($handle, $callback, $priority = 10)
    {
        if ($removed = remove_filter($handle, $callback, $priority)) {
            return $removed;
        }

        /**
         * If the hook was not able to be removed above, then it has not been set yet.
         * Here we add a new listener right before the hook is expected to fire,
         * so that if it is there, we can unhook it just in time.
         */
        return on($handle, function ($given = null) use ($handle, $callback, $priority) {
            remove_filter($handle, $callback, $priority);
            return $given;
        })->withPriority($priority - 1);
    }
endif;
