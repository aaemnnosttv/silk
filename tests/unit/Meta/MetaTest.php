<?php

use Silk\Meta\Meta;
use Illuminate\Support\Collection;

class MetaTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_gets_the_single_value_for_a_post_meta_key()
    {
        update_post_meta(123, 'some_meta_key', 'the value');

        $meta = new Meta('post', 123, 'some_meta_key');
        $this->assertEquals('the value', $meta->get());
        $this->assertEquals('the value', (string) $meta);
    }

    /**
     * @test
     */
    public function it_sets_the_single_value_for_a_post_meta_key()
    {
        $meta = new Meta('post', 123, 'some_meta_key');

        $meta->set('new value');

        $wp_value = get_post_meta(123, 'some_meta_key', true);

        $this->assertEquals('new value', $wp_value);
    }

    /**
     * @test
     */
    public function it_can_update_a_single_value()
    {
        $meta = new Meta('post', 123, 'many');

        add_post_meta(123, 'many', 'one');
        add_post_meta(123, 'many', 'two');
        add_post_meta(123, 'many', 'three');

        $meta->replace('two', 'zwei')
            ->replace('three', 'drei');

        $this->assertSame(['one','zwei','drei'], $meta->all());
    }

    /**
     * @test
     */
    public function it_can_check_for_the_existence_of_any_value()
    {
        $meta = new Meta('post', 123, 'some_nonexistent_meta_key');

        $this->assertFalse($meta->exists());

        $meta->set("I'm ALIVEEEE");

        $this->assertTrue($meta->exists());
    }

    /**
     * @test
     */
    public function it_can_add_meta_for_keys_with_multiple_values()
    {
        $meta = new Meta('post', 123, 'many_values');
        $this->assertCount(0, $meta->all());

        $meta->add('one');
        $meta->add('two');
        $meta->add('three');

        $this->assertCount(3, $meta->all());
    }

    /**
     * @test
     */
    public function it_can_delete_meta_for_a_key()
    {
        $meta = new Meta('post', 123, 'temp');
        $meta->set('this value is about to be deleted');

        $this->assertTrue($meta->exists());

        $meta->delete();

        $this->assertFalse($meta->exists());

        // Multiple values
        $meta->add('one')
            ->add('two')
            ->add('three');

        // delete a specific value
        $meta->delete('one')
            ->delete('three');

        $this->assertSame(['two'], $meta->all());
    }

    /**
     * @test
     */
    public function it_can_return_all_meta_as_an_array_or_a_collection()
    {
        $meta = new Meta('post', 123, 'many_values');
        $meta->add('one')
            ->add('two')
            ->add('three');

        $this->assertSame(['one','two','three'], $meta->all());
        $this->assertInstanceOf(Collection::class, $meta->collect());
    }
}
