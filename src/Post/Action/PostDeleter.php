<?php

namespace Silk\Post\Action;

use Silk\Database\Action;

class PostDeleter extends Action
{
    public function execute()
    {
        if (wp_delete_post($this->model->id, true)) {
            $this->model->refresh();
        }
    }
}
