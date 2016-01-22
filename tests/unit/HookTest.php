<?php

use Silk\Hook;

class HookTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function an_instance_can_be_created_with_just_a_handle()
    {
        $hook = Hook::on('init');

        $this->assertInstanceOf(Hook::class, $hook);
    }

    /**
     * @test
     */
    public function it_uses_a_fluent_api()
    {
        $hook = Hook::on('asdf')
            ->setCallback(function () {})
            ->withPriority(99)
            ->once()
            ->listen();

        $this->assertInstanceOf(Hook::class, $hook);
    }

    /**
     * @test
     */
    public function it_calls_the_callback_we_give_it()
    {
        $data = '';

        $hook = Hook::on('some_action')
            ->setCallback(function ($given) use (&$data) {
                $data = $given;
            })
            ->listen();

        do_action('some_action', 'Howdy!');

        $this->assertEquals($data, 'Howdy!');

        apply_filters('some_action', 'Filter this!');

        $this->assertEquals($data, 'Filter this!');
    }

    /**
     * @test
     */
    public function it_listens_on_the_priority_we_set()
    {
        $data = 'this is passed by reference to the callback';

        $iterate = function ($value) {
            return ++$value;
        };
        add_filter('filterme', $iterate, 1);
        add_filter('filterme', $iterate, 2);
        add_filter('filterme', $iterate, 3);
        add_filter('filterme', $iterate, 4);
        // check here
        add_filter('filterme', $iterate, 6);
        add_filter('filterme', $iterate, 7);
        add_filter('filterme', $iterate, 8);

        Hook::on('filterme')
            ->setCallback(function ($value) use (&$data) {
                $data = $value;
            })
            ->withPriority(5)
            ->listen();

        apply_filters('filterme', 1);

        $this->assertEquals(5, $data);
    }

    /**
     * @test
     */
    public function it_passes_the_correct_number_of_arguments_to_the_callback_automatically()
    {
        $arguments_count = null;

        $hook = Hook::on('testing_arguments_passed')
            ->setCallback(function ($one, $two, $three) use (&$arguments_count) {
                $arguments_count = func_num_args();
            })
            ->listen();

        do_action('testing_arguments_passed', 1, 2, 3);

        $this->assertEquals(3, $arguments_count);

        $arguments_count = null;
        $hook->setCallback(function ($one, $two) use (&$arguments_count) {
            $arguments_count = func_num_args();
        });

        do_action('testing_arguments_passed', 1, 2, 3);

        $this->assertEquals(2, $arguments_count);
    }

    /**
     * @test
     */
    public function it_can_be_set_to_only_fire_once()
    {
        $count = 0;

        Hook::on('only_once_test')
            ->setCallback(function () use (&$count) {
                $count++;
            })
            ->once()
            ->listen();

        do_action('only_once_test');
        do_action('only_once_test');
        do_action('only_once_test');

        $this->assertEquals(1, $count);


        Hook::on('only_once_filtered')
            ->setCallback(function ($value) {
                $value *= 2;
                return $value;
            })
            ->once()
            ->listen();

        $result = apply_filters('only_once_filtered', 1);
        $result = apply_filters('only_once_filtered', $result);
        $result = apply_filters('only_once_filtered', $result);

        $this->assertEquals(2, $result);
    }

    /**
     * @test
     */
    public function it_can_remove_its_hook_if_needed()
    {
        $hook = Hook::on('remove_this_test')
            ->setCallback(function () {
                throw new Exception('Test failed!');
            })
            ->listen();

        $this->assertTrue(has_action('remove_this_test'));

        $hook->remove();

        $this->assertFalse(has_action('remove_this_test'));

        do_action('remove_this_test');
    }
}
