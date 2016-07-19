<?php

namespace Silk\Post\Action;

class PostLoader extends Action
{
    public function execute()
    {
        $this->model->setObject(
            \WP_Post::get_instance($this->model->id)
        );
    }
}
