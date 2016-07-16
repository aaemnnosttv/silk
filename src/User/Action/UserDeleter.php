<?php

namespace Silk\User\Action;

use Silk\Database\Action;

class UserDeleter extends Action
{
    public function execute()
    {
        wp_delete_user($this->model->id);
    }
}
