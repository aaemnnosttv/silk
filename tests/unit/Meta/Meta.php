<?php

use Silk\Meta\Meta;
use Illuminate\Support\Collection;

class MetaModelTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_gets_the_single_value_for_a_post_meta_key()
    {
        $post_id = $this->factory->post->create();
        
        update_post_meta($post_id, 'some_meta_key', 'the value');

        $metaForKey = new Meta('post', $post_id, 'some_meta_key');
        $this->assertEquals('the value', $metaForKey->get());
        $this->assertEquals('the value', $metaForKey->some_meta_key);
        $this->assertEquals('the value', (string) $metaForKey);
    }

    /**
     * @test
     */
    public function it_sets_the_single_value_for_a_post_meta_key()
    {
        $meta = $this->makePostMeta('some_meta_key');
        $meta->set('new value');

        $wp_value = get_post_meta($meta->getObjectId(), 'some_meta_key', true);

        $this->assertEquals('new value', $wp_value);
    }

    /**
     * @test
     */
    public function it_can_check_for_the_existence_of_any_value()
    {
        $meta = $this->makePostMeta('some_nonexistent_meta_key');

        $this->assertFalse($meta->exists());

        $meta->set("I'm ALIVEEEE");

        $this->assertTrue($meta->exists());
    }

    
    /**
     * @test
     */
    public function it_can_add_meta_for_keys_with_multiple_values()
    {
        $meta = $this->makePostMeta('many_values');
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
        $meta = $this->makePostMeta('temp');
        $meta->set('this value is about to be deleted');

        $this->assertTrue($meta->exists());

        $meta->delete();

        $this->assertFalse($meta->exists());

        // Multiple values
        $meta->add('one');
        $meta->add('two');

        // delete a specific value
        $meta->delete('one');

        $this->assertFalse($meta->all()->contains('one'));
    }


    /**
     * @test
     */
    public function it_returns_all_meta_as_a_collection()
    {
        $meta = $this->makePostMeta('many_values');

        $this->assertInstanceOf(Collection::class, $meta->all());
    }

    /**
     * @param null $key
     *
     * @return Meta
     */
    protected function makePostMeta($key = null)
    {
        return new Meta('post', $this->factory->post->create(), $key);
    }
}
