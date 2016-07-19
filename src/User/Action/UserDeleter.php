<?php

namespace Silk\User\Action;

class UserDeleter extends Action
{
    public function execute()
    {
        wp_delete_user($this->model->id);
    }
}
