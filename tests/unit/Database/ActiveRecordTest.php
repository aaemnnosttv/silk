<?php

use Silk\Database\NullAction;
use Silk\Contracts\Executable;

class ActiveRecordTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_doesnt_do_anything_if_a_non_existent_action_is_called()
    {
        $record = new Record($this);

        $this->assertNull($record->doNothing());
    }
}

class Record extends Silk\Database\ActiveRecord
{
    protected $testCase;

    public function __construct($testCase)
    {
        $this->testCase = $testCase;
    }

    public function doNothing()
    {
        $this->activeAction('');
    }

    protected function actionClasses()
    {
        return [];
    }

    protected function executeAction(Executable $action)
    {
        $this->testCase->assertInstanceOf(NullAction::class, $action);

        $this->testCase->assertNull($action->execute());
    }
}
