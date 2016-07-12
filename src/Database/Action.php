<?php

namespace Silk\Database;

use Silk\Type\Model;
use Silk\Contracts\Executable;

abstract class Action implements Executable
{
    /**
     * The model instance
     * @var Model
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
