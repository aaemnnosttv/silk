<?php

use Silk\Event\Hook;

class HookTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function an_instance_can_be_created_with_just_a_handle()
    {
        $hook = Hook::on('init');
        $easy = on('init', function () {});

        $this->assertInstanceOf(Hook::class, $hook);
        $this->assertInstanceOf(Hook::class, $easy);
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

        $data = '';
        $lazy = on('quick', function ($given) use (&$data) {
            $data = $given;
        });

        do_action('quick', 'yo');

        $this->assertEquals($data, 'yo');
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
    public function it_passes_all_arguments_to_a_callback_that_has_no_parameters()
    {
        $passed = 0;
        Hook::on('test_all_arguments_passed')
            ->setCallback(function () use (&$passed) {
                $passed = func_num_args();
            })->listen();

        do_action('test_all_arguments_passed', 1, 2, 3);

        $this->assertEquals(3, $passed);
    }


    /**
     * @test
     */
    public function it_can_limit_the_number_of_times_the_callback_is_invoked()
    {
        $count = 0;

        Hook::on('three_times_only_test')
            ->setCallback(function () use (&$count) {
                $count++;
            })
            ->listen()
            ->onlyXtimes(3);

        do_action('three_times_only_test');
        do_action('three_times_only_test');
        do_action('three_times_only_test');
        do_action('three_times_only_test');
        do_action('three_times_only_test');
        do_action('three_times_only_test');

        $this->assertEquals(3, $count);
    }

    /**
     * @test
     */
    public function it_has_a_helper_method_for_bypassing_the_callback()
    {
        $count = 0;

        $hook = Hook::on('bypass_test')
            ->setCallback(function () use (&$count) {
                $count++;
            })
            ->listen();

        do_action('bypass_test');
        do_action('bypass_test');
        do_action('bypass_test');
        do_action('bypass_test');
        do_action('bypass_test'); // 5

        $hook->bypass(); // callback will not be triggered again

        do_action('bypass_test');
        do_action('bypass_test');
        do_action('bypass_test');

        $this->assertEquals(5, $count);
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

    /**
     * @test
     */
    public function it_has_a_helper_function_for_removing_hooks_now_and_in_the_future()
    {
        // exhibit A
        add_action('hook_one', 'cb_one');
        $this->assertTrue(off('hook_one', 'cb_one'));

        // exhibit B
        $boom = function () {
            throw Exception('This should be removed or the test will fail!');
        };
        $hook = off('hook_two', $boom);
        $this->assertInstanceOf(Hook::class, $hook);
        // could be added waaaay later, sometime, we don't even know
        add_action('hook_two', $boom);

        do_action('hook_two');
    }

    /**
     * @test
     */
    public function it_handles_different_callable_syntaxes()
    {
        Hook::on('test_function_name_as_string')
            ->setCallback('aNormalFunction')
            ->listen();

        do_action('test_function_name_as_string');

        Hook::on('test_static_method_as_string')
            ->setCallback('CallMy::staticMethod')
            ->listen();

        do_action('test_static_method_as_string');

        Hook::on('test_static_method_as_array')
            ->setCallback(['CallMy', 'staticMethod'])
            ->listen();

        do_action('test_static_method_as_array');

        Hook::on('test_instance_method_as_array')
            ->setCallback([new CallMy, 'instanceMethod'])
            ->listen();

        do_action('test_instance_method_as_array');
    }

    /**
     * @test
     */
    function it_can_accept_a_condition_to_control_the_invocation_of_the_callback()
    {
        on('conditional_test', static function () {
            throw new Exception("Don't let this happen!");
        })->onlyIf(static function ($should_blow_up) {
            return $should_blow_up;
        });

        do_action('conditional_test', false);

        $sum = 0;

        on('conditional_addition', function ($num) use (&$sum) {
            $sum += $num;
        })->onlyIf(function ($num) {
            return 0 === $num % 2; // return true for even numbers
        });

        do_action('conditional_addition', 1); // ignored
        do_action('conditional_addition', 2); // HIT
        do_action('conditional_addition', 3); // ignored
        do_action('conditional_addition', 4); // HIT
        do_action('conditional_addition', 5); // ignored
        do_action('conditional_addition', 6); // HIT
        do_action('conditional_addition', 7); // ignored

        $this->assertEquals(2 + 4 + 6, $sum);

        // complex using filter

        on('complex_condition', function ($names, $name) {
            $names[] = $name;
            return $names;
        })->onlyIf(function ($names, $name) {
            return is_string($name) && strlen($name) > 3;
        })->onlyIf(function ($names, $name, $status) {
            return in_array($status, ['naughty', 'nice','salamander']);
        })->onlyIf(function ($names, $name) {
            return ! in_array($name, $names);
        });

        $names = [];
        $names = apply_filters('complex_condition', $names, 'Donald', 'naughty');
        $names = apply_filters('complex_condition', $names, 'Hillary', 'naughty');
        $names = apply_filters('complex_condition', $names, 'Barack', 'in-the-house'); // x
        $names = apply_filters('complex_condition', $names, 'Ted', 'nice'); // x
        $names = apply_filters('complex_condition', $names, 'Bill', 'nice');
        $names = apply_filters('complex_condition', $names, 'Evil Bill', 'naughty');
        $names = apply_filters('complex_condition', $names, 'Donald', 'salamander');
        $names = apply_filters('complex_condition', $names, 'Donald', 'salamander');
        $names = apply_filters('complex_condition', $names, 'Donald', 'salamander');

        $this->assertSame(['Donald', 'Hillary', 'Bill', 'Evil Bill'], $names);
    }

    /**
     * @test
     */
    public function it_returns_the_first_parameter_if_the_callback_returns_nothing()
    {
        $spy = 'spy';

        on('filter_as_action_test', function () use (&$spy) {
            $spy = 'spider';
        });

        $filtered = apply_filters('filter_as_action_test', 'something');

        $this->assertSame('spider', $spy); // ensures callback was called
        /**
         * Our callback returned nothing, therefore Hook will return for us.
         */
        $this->assertSame('something', $filtered);
    }

}

function aNormalFunction()
{
}

class CallMy
{
    public static function staticMethod()
    {
    }

    public function instanceMethod()
    {
    }
}
