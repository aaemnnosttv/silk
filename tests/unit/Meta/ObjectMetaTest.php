<?php

use Silk\Meta\Meta;
use Silk\Meta\ObjectMeta;
use Illuminate\Support\Collection;

class ObjectMetaTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_proxies_properties_to_single_meta_key_values()
    {
        $post_id = $this->factory->post->create(['post_title' => 'Test me, baby']);

        update_post_meta($post_id, 'some_meta_key', 'turkey');

        $meta = new ObjectMeta('post', $post_id);
        // GET
        $this->assertEquals('turkey', $meta->some_meta_key);
        // SET
        $meta->some_meta_key = 'chicken';
        $updated_value = get_post_meta($post_id, 'some_meta_key', true);

        $this->assertEquals('chicken', $meta->some_meta_key);
    }

    /**
     * @test
     */
    public function it_can_get_a_dedicated_meta_object_for_a_given_key()
    {
        $post_id = $this->factory->post->create(['post_title' => 'Test me, baby']);

        $postMeta = new ObjectMeta('post', $post_id);

        $this->assertInstanceOf(Meta::class, $postMeta->get('some_meta_key'));
    }

    /**
     * @test
     */
    public function it_can_return_all_meta_as_a_collection()
    {
        $post_id = $this->factory->post->create(['post_title' => 'Test me, baby']);

        $meta = new ObjectMeta('post', $post_id);

        $this->assertInstanceOf(Collection::class, $meta->collect());
    }
}
