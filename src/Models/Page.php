<?php

namespace Silk\Models;

use WP_Post;

class Page extends Post
{
    /**
     * The post type of the post this model wraps
     * @var string
     */
    const POST_TYPE = 'page';
}
