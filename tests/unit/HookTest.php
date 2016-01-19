<?php

use Silk\Hook;

class HookTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function an_instance_can_be_created_with_just_a_handle()
    {
        $hook = Hook::make('init');

        $this->assertInstanceOf(Hook::class, $hook);
    }

    /**
     * @test
     */
    public function it_uses_a_fluent_api()
    {
        $hook = Hook::make('asdf')
            ->setCallback(function () {})
            ->listen();

        $this->assertInstanceOf(Hook::class, $hook);
    }

    /**
     * @test
     */
    public function it_calls_the_callback_we_give_it()
    {
        $data = '';

        $hook = Hook::make('some_action')
            ->setCallback(function ($given) use (&$data) {
                $data = $given;
            })
            ->listen();

        do_action('some_action', 'Howdy!');

        $this->assertEquals($data, 'Howdy!');

        apply_filters('some_action', 'Filter this!');

        $this->assertEquals($data, 'Filter this!');
    }
}
