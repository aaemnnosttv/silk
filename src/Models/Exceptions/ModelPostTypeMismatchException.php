<?php

namespace Silk\Models\Exceptions;

use WP_Post;
use Silk\Models\Post;

class ModelPostTypeMismatchException extends \RuntimeException
{
    const MESSAGE_FORMAT = '{modelClass} instantiated with post of type "{givenPostType}", but requires a post of type "{modelPostType}".';

    public function __construct(Post $model, WP_Post $post)
    {
        $this->model = $model;
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
            get_class($this->model),
            $this->post->post_type,
            $this->model->type
        ], static::MESSAGE_FORMAT);
    }
}
