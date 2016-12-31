<?php

namespace Silk\Support;

class NegatedCallback extends Callback
{
    /**
     * Call the target callable, with an array of arguments
     *
     * @param  array $arguments The parameters to be passed to the callback, as an indexed array.
     *
     * @return mixed             Returns the return value of the callback, or FALSE on error.
     */
    public function callArray(array $arguments = [])
    {
        return ! parent::callArray($arguments);
    }
}
