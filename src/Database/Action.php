<?php

namespace Silk\Database;

use Silk\Contracts\Executable;

abstract class Action implements Executable
{
    /**
     * The model instance
     * @var ActiveRecord
     */
    protected $model;

    /**
     * Action Constructor.
     *
     * @param ActiveRecord $model The model performing the action
     */
    public function __construct(ActiveRecord $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the action.
     * 
     * @return void
     */
    abstract public function execute();
}
