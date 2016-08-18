<?php

use Silk\Meta\Meta;
use Silk\Meta\ObjectMeta;
use Illuminate\Support\Collection;

class ObjectMetaTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_can_get_a_dedicated_meta_object_for_a_given_key()
    {
        $post_id = $this->factory->post->create();

        $postMeta = new ObjectMeta('post', $post_id);

        $this->assertInstanceOf(Meta::class, $postMeta->get('some_meta_key'));
    }

    /**
     * @test
     */
    public function it_can_return_all_meta_as_a_collection()
    {
        $post_id = $this->factory->post->create();

        $meta = new ObjectMeta('post', $post_id);

        $this->assertInstanceOf(Collection::class, $meta->collect());

        foreach ($meta->collect() as $metaForKey) {
            $this->assertInstanceOf(Meta::class, $metaForKey);
        }
    }

    /**
     * @test
     */
    public function it_can_return_all_meta_as_an_array()
    {
        /**
         * Use a made up post ID so that we can be sure these are the only meta values.
         * @var integer
         */
        $post_id = 100;
        $meta = new ObjectMeta('post', $post_id);

        update_post_meta($post_id, 'a', '1', true);
        update_post_meta($post_id, 'b', '2', true);

        $this->assertSame([
                'a' => ['1'],
                'b' => ['2']
            ],
            get_metadata('post', $post_id)
        );
    }

    /**
     * @test
     */
    public function it_has_readonly_properties()
    {
        $meta = new ObjectMeta('post', 123);

        $this->assertSame('post', $meta->type);
        $this->assertSame(123, $meta->id);

        $this->assertNull($meta->non_existent);
    }

    /**
     * @test
     */
    public function it_has_a_fluent_setter()
    {
        $meta = new ObjectMeta('post', 123);

        $meta->set('a', 'b')
            ->set('foo', 'bar');

        $this->assertSame([
                'a'   => ['b'],
                'foo' => ['bar']
            ],
            get_metadata('post', 123)
        );
    }

}
