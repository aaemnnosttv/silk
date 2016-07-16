<?php

namespace Silk\Term\Action;

use Silk\Database\Action;

class TermLoader extends Action
{
    public function execute()
    {
        $this->model->setObject(
            \WP_Term::get_instance($this->model->id, $this->model->typeId())
        );
    }
}
