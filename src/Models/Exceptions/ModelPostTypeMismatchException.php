<?php

namespace Silk\Models\Exceptions;

use WP_Post;
use Silk\Models\Post;

class ModelPostTypeMismatchException extends \RuntimeException
{
    const MESSAGE_FORMAT = '{modelClass} instantiated with post of type "{givenPostType}", but requires a post of type "{modelPostType}".';

    public function __construct($modelClass, WP_Post $post)
    {
        $this->modelClass = $modelClass;
        $this->post = $post;
        $this->message = $this->formatMessage();
    }

    protected function formatMessage()
    {
        return str_replace([
            '{modelClass}',
            '{givenPostType}',
            '{modelPostType}'
        ], [
            $this->modelClass,
            $this->post->post_type,
            constant($this->modelClass . '::POST_TYPE')
        ], static::MESSAGE_FORMAT);
    }
}
