<?php

namespace Silk\Post\Action;

use Silk\Exception\WP_ErrorException;

class PostSaver extends Action
{
    public function execute()
    {
        if (! $this->model->id) {
            $result = wp_insert_post($this->model->object->to_array(), true);
        } else {
            $result = wp_update_post($this->model->object, true);
        }

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        $this->model->setId($result)
            ->refresh();
    }
}
