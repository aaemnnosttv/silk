<?php

namespace Silk\Post\Action;

use Silk\Database\Action;

class PostLoader extends Action
{
    public function execute()
    {
        $this->model->setObject(
            \WP_Post::get_instance($this->model->id)
        );
    }
}
