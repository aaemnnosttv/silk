<?php

use Silk\Models\Meta;

class MetaModelTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_gets_the_single_value_for_a_post_meta_key()
    {
        $post_id = $this->factory->post->create(['post_title' => 'Test me, baby']);
        update_post_meta($post_id, 'some_meta_key', 'the value');

        $metaForKey = new Meta('post', $post_id, 'some_meta_key');
        $this->assertEquals('the value', $metaForKey->get());
    }

    /**
     * @test
     */
    public function it_sets_the_single_value_for_a_post_meta_key()
    {
        $post_id = $this->factory->post->create(['post_title' => 'Test me, baby']);

        $meta = new Meta('post', $post_id, 'some_meta_key');
        $meta->set('new value');

        $wp_value = get_post_meta($post_id, 'some_meta_key', 'the value');

        $this->assertEquals('new value', $wp_value);
    }
}
