<?php

namespace Silk\Term\Action;

class TermLoader extends Action
{
    public function execute()
    {
        $this->model->setObject(
            \WP_Term::get_instance($this->model->id, $this->model->taxonomy)
        );
    }
}
