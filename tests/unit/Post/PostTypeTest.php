<?php

use Silk\Post\PostType;

class PostTypeTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_can_be_constructed_with_a_slug_or_post_type_object()
    {
        $somePostType = new PostType('some-post-type');
        $post = new PostType(get_post_type_object('post'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    function it_blows_up_if_constructed_without_a_slug_or_post_type_object()
    {
        new PostType(new stdClass);
    }

    /**
     * @test
     */
    function it_has_a_named_constructor_for_making_a_new_instance()
    {
        $this->assertInstanceOf(PostType::class, PostType::make('new-type'));
    }

    /**
     * @test
     */
    function it_can_register_the_post_type()
    {
        $this->assertPostTypeNotExists('some-post-type');

        PostType::make('some-post-type')->register();

        $this->assertPostTypeExists('some-post-type');
    }

    /**
    * @test
    * @expectedException Silk\Post\Exception\InvalidPostTypeNameException
    */
    function it_blows_up_if_the_post_type_slug_is_too_long()
    {
        PostType::make('twenty-character-limit');
    }

    /**
    * @test
    * @expectedException Silk\Post\Exception\InvalidPostTypeNameException
    */
    function it_blows_up_if_the_post_type_slug_is_too_short()
    {
        PostType::make('');
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
        PostType::make('non-existent')->unregister();
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
    function it_can_load_a_new_instance_from_an_existing_post_type()
    {
        $type = PostType::load('post');
        $this->assertInstanceOf(\stdClass::class, $type->object());
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
    function it_accepts_an_array_or_parameters_for_supported_features()
    {
        PostType::make('bread')->supports(['flour', 'water'])->register();

        $this->assertTrue(post_type_supports('bread', 'flour'));
        $this->assertTrue(post_type_supports('bread', 'water'));

        PostType::make('butter')->supports('bread', 'spreading')->register();

        $this->assertTrue(post_type_supports('butter', 'bread'));
        $this->assertTrue(post_type_supports('butter', 'spreading'));
    }

    /**
     * @test
     */
    function it_can_get_and_set_arbitrary_values_for_the_registration_arguments()
    {
        $type = PostType::make('stuff')->set('mood', 'happy');

        $this->assertSame('happy', $type->get('mood'));
    }

    /**
     * @test
     */
    function it_has_dedicated_methods_for_public_visibility()
    {
        $public = PostType::make('a-public-type')->open();
        $this->assertTrue($public->get('public'));

        $private = PostType::make('a-private-type')->closed();
        $this->assertFalse($private->get('public'));
    }

    /**
     * @test
     */
    function it_has_dedicated_methods_for_user_interface()
    {
        $ui = PostType::make('ui-having')->withUI();
        $this->assertTrue($ui->get('show_ui'));

        $no_ui = PostType::make('no-ui')->noUI();
        $this->assertFalse($no_ui->get('show_ui'));
    }



    // *******************************************************************

    /**
     * [assertPostTypeExists description]
     * @param  [type] $slug [description]
     * @return [type]       [description]
     */
    protected function assertPostTypeExists($slug)
    {
        $this->assertTrue(post_type_exists($slug));
    }

    /**
     * [assertPostTypeExists description]
     * @param  [type] $slug [description]
     * @return [type]       [description]
     */
    protected function assertPostTypeNotExists($slug)
    {
        $this->assertFalse(post_type_exists($slug));
    }
}
