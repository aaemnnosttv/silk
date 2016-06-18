<?php

use Silk\Post\PostType;
use Silk\Post\PostTypeBuilder;

class PostTypeTest extends WP_UnitTestCase
{
    use PostTypeAssertions;

    /**
    * @test
    */
    function it_takes_a_post_type_object_in_the_constructor()
    {
        new PostType(get_post_type_object('post'));
    }

    /**
    * @test
    */
    function it_has_a_named_constructor_for_creating_a_new_instance_from_an_existing_post_type()
    {
        $this->assertInstanceOf(PostType::class, PostType::load('post'));
        $this->assertInstanceOf(PostType::class, PostType::make('page'));
    }

    /**
     * @test
     * @expectedException Silk\Post\Exception\NonExistentPostTypeException
     */
    function it_blows_up_if_loading_a_non_existent_post_type()
    {
        PostType::load('non-existent-type');
    }

    /**
     * @test
     */
    function the_make_method_returns_a_new_instance_for_existing_types_otherwise_a_builder_instance()
    {
        $this->assertInstanceOf(PostType::class, PostType::make('post'));
        $this->assertInstanceOf(PostTypeBuilder::class, PostType::make('mega-post'));
    }

    /**
     * @test
     */
    function it_can_unregister_the_post_type()
    {
        $type = PostType::make('temporary')->register();

        $this->assertPostTypeExists('temporary');

        $type->unregister();

        $this->assertPostTypeNotExists('temporary');
    }

    /**
     * @test
     * @expectedException Silk\Post\Exception\NonExistentPostTypeException
     */
    function it_blows_up_if_it_tries_to_unregister_a_nonexistent_type()
    {
        $type = PostType::make('non-existent')->register();

        unregister_post_type('non-existent');

        $type->unregister();
    }

    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    function it_blows_up_if_it_tries_to_unregister_a_built_in_post_type()
    {
        PostType::load('post')->unregister();
    }

    /**
     * @test
     */
    function it_can_check_if_the_post_type_exists()
    {
        $this->assertTrue(PostType::exists('post'));
        $this->assertFalse(PostType::exists('post-it-note'));
    }

    /**
     * @test
     */
    function it_has_methods_for_adding_and_removing_support_for_post_type_features()
    {
        $type = PostType::load('post')
            ->addSupportFor('dollars', 'cents');

        $this->assertTrue($type->supports('title', 'editor'));
        $this->assertFalse($type->supports('euros'));

        $type->removeSupportFor('dollars', 'cents')->addSupportFor('euros');

        $this->assertFalse($type->supports('bits-of-string', 'euros', 'monopoly-money'));
        $this->assertTrue($type->supports('euros'));
    }

    /**
     * @test
     */
    function it_has_readonly_magic_properties()
    {
        $type = PostType::load('post');

        $this->assertSame('post', $type->slug);
        $this->assertSame('Post', $type->one);
        $this->assertSame('Posts', $type->many);

        $this->assertNull($type->nonExistentProperty);
    }

}
