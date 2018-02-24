<?php

namespace Silk\Support;

/**
 * The Tightenco namespaced Collection class has only been available since 5.5.33
 * with aliases for the previous Illuminate\Support class for backwards compat.
 *
 * For PHP 5.6, v5.4 of the library will be installed, thus the namespaced version
 * won't be available.
 */

// @codeCoverageIgnoreStart
if (class_exists('Tightenco\Collect\Support\Collection')) {
    class Collection extends \Tightenco\Collect\Support\Collection
    {
    }
} else {
    class Collection extends \Illuminate\Support\Collection
    {
    }
}
// @codeCoverageIgnoreEnd
