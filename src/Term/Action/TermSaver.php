<?php

namespace Silk\Term\Action;

use Silk\Database\Action;
use Silk\Exception\WP_ErrorException;

class TermSaver extends Action
{
    public function execute()
    {
        $taxonomy = $this->model->typeId();

        if ($this->model->id) {
            $ids = wp_update_term($this->model->id, $taxonomy, $this->model->object->to_array());
        } else {
            $ids = wp_insert_term($this->model->name, $taxonomy, $this->model->object->to_array());
        }

        if (is_wp_error($ids)) {
            throw new WP_ErrorException($ids);
        }

        $this->model->setId($ids['term_id']);
        $this->model->refresh();
    }
}
