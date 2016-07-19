<?php

namespace Silk\User\Action;

use Silk\Exception\WP_ErrorException;

class UserSaver extends Action
{
    public function execute()
    {
        if (! $this->model->id) {
            $result = wp_insert_user($this->model->object);
        } else {
            $result = wp_update_user($this->model->object);
        }

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        $this->model->setId($result);
    }
}
