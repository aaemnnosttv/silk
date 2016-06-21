<?php

namespace Silk\Post\Exception;

use WP_Post;
use Silk\Models\Post;

class ModelPostTypeMismatchException extends \RuntimeException
{
    const MESSAGE_FORMAT = '{modelClass} instantiated with post of type "{givenPostType}", but requires a post of type "{modelPostType}".';

    /**
     * The model's full class name
     * @var string
     */
    protected $modelClass;
    /**
     * The post object
     * @var WP_Post
     */
    protected $post;

    /**
     * ModelPostTypeMismatchException Constructor.
     *
     * @param string  $modelClass  The model's full class name
     * @param WP_Post $post        The post object
     */
    public function __construct($modelClass, WP_Post $post)
    {
        $this->modelClass = $modelClass;
        $this->post = $post;
        $this->message = str_replace([
            '{modelClass}',
            '{givenPostType}',
            '{modelPostType}'
        ], [
            $this->modelClass,
            $this->post->post_type,
            call_user_func([$this->modelClass, 'postTypeId'])
        ], static::MESSAGE_FORMAT);
    }
}
