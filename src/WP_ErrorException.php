<?php

namespace Silk;

use WP_Error;

class WP_ErrorException extends \RuntimeException
{

    public function __construct(WP_Error $error)
    {
        $this->message = $error->get_error_message();
    }

}
