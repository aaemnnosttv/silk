<?php

use Silk\WordPress\Post\Post;
use Silk\Post\QueryBuilder;

class PostTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_can_create_a_post_from_a_new_instance()
    {
        $model = new Post();
        $this->assertNull($model->id);
        $this->assertInstanceOf(WP_Post::class, $model->post);

        $model->post_title = 'the title';
        $model->save();

        $this->assertNotEmpty($model->post->ID);
    }

    /**
     * @test
     */
    function it_can_be_instantiated_from_a_wp_post_object()
    {
        $post = $this->factory->post->create_and_get();
        $model = Post::fromWpPost($post);

        $this->assertSame($post->ID, $model->id);
    }

    /**
     * @test
     */
    public function it_can_find_a_post_by_the_id()
    {
        $post_id = $this->factory->post->create();
        $model   = Post::fromID($post_id);

        $this->assertInstanceOf(Post::class, $model);
        $this->assertInstanceOf(WP_Post::class, $model->post);
        $this->assertEquals($post_id, $model->id);
    }

    /**
     * @test
     */
    public function it_can_find_a_post_by_the_slug()
    {
        $the_slug = 'foo-bar-slug';
        $post_id  = $this->factory->post->create(['post_name' => $the_slug]);
        $model    = Post::fromSlug($the_slug);

        $this->assertEquals($post_id, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\Post\Exception\PostNotFoundException
     */
    public function it_blows_up_if_no_post_is_found_for_given_slug()
    {
        Post::fromSlug('no-post-here');
    }

    /**
     * @test
     * @expectedException Silk\Post\Exception\PostNotFoundException
     */
    public function it_blows_up_if_no_post_exists_for_given_id()
    {
        Post::fromID(123958723409817209872350872395872304);
    }

    /**
     * @test
     */
    function it_can_be_created_from_the_global_post()
    {
        global $post;

        $post = $this->factory->post->create_and_get();
        $model = Post::fromGlobal();

        $this->assertSame($post->ID, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\Post\Exception\PostNotFoundException
     */
    function it_blows_up_if_instantiated_from_an_empty_global_post()
    {
        Post::fromGlobal();
    }

    /**
     * @test
     */
    public function it_proxies_property_access_to_the_post_if_not_available_on_the_instance()
    {
        $post = $this->factory->post->create_and_get();
        $model = new Post($post);

        $this->assertEquals($post->post_date, $model->post_date);
        $this->assertEquals($post->post_excerpt, $model->post_excerpt);

        $this->assertEmpty($model->some_property);
        $model->meta('some_property')->set('awesome');

        $this->assertSame('awesome', $post->some_property);
        $this->assertSame('awesome', $model->some_property);
    }

    /**
     * @test
     */
    public function it_provides_an_object_for_interacting_with_the_post_meta()
    {
        $post_id = $this->factory->post->create();
        update_post_meta($post_id, 'new_meta', 'so fresh');

        $post_meta = get_post_custom($post_id);
        $model = Post::fromID($post_id);

        $this->assertEquals($post_meta, $model->meta()->toArray());

        $this->assertInstanceOf(Silk\Meta\Meta::class, $model->meta('new_meta'));
    }

    /**
     * @test
     */
    public function it_can_create_a_new_post()
    {
        $model = Post::create([
            'post_title' => 'Foo'
        ]);
        $this->assertInstanceOf(Post::class, $model);
        $this->assertGreaterThan(0, $model->id);

        $post = get_post($model->id);

        $this->assertEquals($post->ID, $model->id);
    }


    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    public function it_blows_up_if_required_attributes_are_not_passed_when_created()
    {
        Post::create();
    }

    /**
     * @test
     */
    function it_creates_a_post_of_the_models_type()
    {
        $model = CustomTypeStub::create(['post_title' => 'This is just a test']);

        $this->assertEquals(CustomTypeStub::POST_TYPE, $model->post_type);
    }

    /**
     * @test
     */
    function it_can_delete_itself()
    {
        $post_id = $this->factory->post->create();
        $model = Post::fromID($post_id);

        $this->assertSame($post_id, $model->id);
        $this->assertInstanceOf(WP_Post::class, get_post($post_id));

        $model->delete();

        $this->assertNull(get_post($post_id));
        $this->assertNull($this->post);
    }


    /**
     * @test
     */
    public function it_handles_trashing_and_untrashing()
    {
        $model = Post::create([
            'post_title' => 'Yay, I\'m Alive!',
            'post_status' => 'publish'
        ]);

        $this->assertEquals('publish', get_post_status($model->id));

        $model->trash();

        $this->assertEquals('trash', get_post_status($model->id));

        $model->untrash();

        $this->assertEquals('publish', get_post_status($model->id));
    }

    /**
     * @test
     */
    public function it_has_a_method_for_refreshing_the_wrapped_post()
    {
        $model = Post::create([
            'post_title' => 'OG Title'
        ]);

        // the post is modified elsewhere
        wp_update_post([
            'ID' => $model->id,
            'post_title' => 'Changed'
        ]);

        $this->assertEquals('OG Title', $model->post_title);
        $this->assertEquals('Changed', get_the_title($model->id));

        $model->refresh();

        $this->assertEquals('Changed', $model->post_title);
    }

    /**
     * @test
     */
    public function it_can_save_changes_back_to_the_database()
    {
        $model = Post::create([
            'post_title' => 'OG Title'
        ]);

        $model->post_title = 'Changed';

        $model->save();

        $this->assertEquals('Changed', get_the_title($model->id));
    }

    /**
     * @test
     */
    function it_offers_static_methods_for_querying()
    {
        $this->assertInstanceOf(QueryBuilder::class, Post::query());
    }

    /**
     * @test
     */
    function it_has_a_static_method_for_starting_a_new_query_for_all_posts_of_type()
    {
        $this->factory->post->create_many(15);

        $this->assertCount(15, Post::all()->results());
    }

    /**
     * @test
     */
    function it_proxies_non_existent_static_methods_to_the_builder()
    {
        $this->assertInstanceOf(
            QueryBuilder::class,
            Post::limit(1)
        );
    }

}

class CustomTypeStub extends Post
{
    const POST_TYPE = 'cpt_stub';

    public static function __register()
    {
        register_post_type(static::POST_TYPE);
    }
}
