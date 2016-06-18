<?php

use Silk\Post\PostTypeBuilder;

class PostTypeBuilderTest extends WP_UnitTestCase
{
    use PostTypeAssertions;

    /**
     * @test
     */
    function it_can_be_constructed_with_a_slug()
    {
        new PostTypeBuilder('some-type');
    }

    /**
    * @test
    */
    function it_has_a_named_constructor_for_creating_a_new_instance()
    {
        $this->assertInstanceOf(PostTypeBuilder::class, PostTypeBuilder::make('new-type'));
    }

    /**
     * @test
     */
    function it_can_register_the_post_type()
    {
        $this->assertPostTypeNotExists('some-post-type');

        $object = PostTypeBuilder::make('some-post-type')->register()->object();

        $this->assertSame($object, get_post_type_object('some-post-type'));

        $this->assertPostTypeExists('some-post-type');
    }

    /**
    * @test
    * @expectedException Silk\Post\Exception\InvalidPostTypeNameException
    */
    function it_blows_up_if_the_post_type_slug_is_too_long()
    {
        PostTypeBuilder::make('twenty-character-limit');
    }

    /**
    * @test
    * @expectedException Silk\Post\Exception\InvalidPostTypeNameException
    */
    function it_blows_up_if_the_post_type_slug_is_too_short()
    {
        PostTypeBuilder::make('');
    }

    /**
     * @test
     */
    function it_accepts_an_array_or_parameters_for_supported_features()
    {
        PostTypeBuilder::make('bread')->supports(['flour', 'water'])->register();

        $this->assertTrue(post_type_supports('bread', 'flour'));
        $this->assertTrue(post_type_supports('bread', 'water'));

        PostTypeBuilder::make('butter')->supports('bread', 'spreading')->register();

        $this->assertTrue(post_type_supports('butter', 'bread'));
        $this->assertTrue(post_type_supports('butter', 'spreading'));
    }

    /**
     * @test
     */
    function it_can_get_and_set_arbitrary_values_for_the_registration_arguments()
    {
        $type = PostTypeBuilder::make('stuff')
            ->set('mood', 'happy')
            ->register();

        $object = get_post_type_object('stuff');

        $this->assertSame('happy', $object->mood);
    }

    /**
     * @test
     */
    function it_has_dedicated_methods_for_public_visibility()
    {
        $public = PostTypeBuilder::make('a-public-type')->open();
        $this->assertTrue($public->get('public'));

        $private = PostTypeBuilder::make('a-private-type')->closed();
        $this->assertFalse($private->get('public'));
    }

    /**
     * @test
     */
    function it_has_dedicated_methods_for_user_interface()
    {
        $ui = PostTypeBuilder::make('ui-having')->withUI();
        $this->assertTrue($ui->get('show_ui'));

        $no_ui = PostTypeBuilder::make('no-ui')->noUI();
        $this->assertFalse($no_ui->get('show_ui'));
    }

    /**
     * @test
     */
    function it_has_methods_for_setting_the_labels()
    {
        $book = PostTypeBuilder::make('book')
            ->oneIs('Book')
            ->manyAre('Books');

        $this->assertSame('Book', $book->one);
        $this->assertSame('Books', $book->many);
    }

    /**
     * @test
     */
    function it_makes_the_slug_available_as_a_read_only_property()
    {
        $this->assertSame('book', PostTypeBuilder::make('book')->slug);
    }

    /**
     * @test
     */
    function it_returns_null_for_non_existent_properties()
    {
        $this->assertNull(PostTypeBuilder::make('book')->nonExistentProperty);
    }

}
