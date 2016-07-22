<?php

namespace Silk\Term\Action;

class TermDeleter extends Action
{
    public function execute()
    {
        if (wp_delete_term($this->model->id, $this->model->taxonomy)) {
            $this->model->setObject(new \WP_Term(new \stdClass));
        }
    }
}
