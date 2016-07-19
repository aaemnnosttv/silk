<?php

namespace Silk\User\Action;

use Silk\User\Model;
use Silk\Contracts\Executable;

abstract class Action implements Executable
{
    /**
     * The model instance
     * @var \Silk\User\Model
     */
    protected $model;

    /**
     * Action Constructor.
     *
     * @param Model $model The model performing the action
     */
    public function __construct(Model $model)
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
