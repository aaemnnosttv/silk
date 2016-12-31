<?php

namespace Silk\Exception;

use WP_Error;

class WP_ErrorException extends \RuntimeException
{
    /**
     * WP_ErrorException Constructor.
     *
     * @param WP_Error $error  The error to set the message from
     */
    public function __construct(WP_Error $error)
    {
        $this->code = $error->get_error_code();
        $this->message = $error->get_error_message();
    }
}
