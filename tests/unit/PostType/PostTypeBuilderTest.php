<?php

use Silk\PostType\Builder;

class PostTypeBuilderTest extends WP_UnitTestCase
{
    use PostTypeAssertions;

    /**
     * @test
     */
    function it_can_be_constructed_with_a_slug()
    {
        new Builder('some-type');
    }

    /**
    * @test
    */
    function it_has_a_named_constructor_for_creating_a_new_instance()
    {
        $this->assertInstanceOf(Builder::class, Builder::make('new-type'));
    }

    /**
     * @test
     */
    function it_can_register_the_post_type()
    {
        $this->assertPostTypeNotExists('some-post-type');

        $object = Builder::make('some-post-type')->register()->object();

        $this->assertSame($object, get_post_type_object('some-post-type'));

        $this->assertPostTypeExists('some-post-type');
    }

    /**
    * @test
    * @expectedException Silk\PostType\Exception\InvalidPostTypeNameException
    */
    function it_blows_up_if_the_post_type_slug_is_too_long()
    {
        Builder::make('twenty-character-limit')->register();
    }

    /**
    * @test
    * @expectedException Silk\PostType\Exception\InvalidPostTypeNameException
    */
    function it_blows_up_if_the_post_type_slug_is_too_short()
    {
        Builder::make('')->register();
    }

    /**
     * @test
     */
    function it_accepts_an_array_or_parameters_for_supported_features()
    {
        Builder::make('bread')->supports(['flour', 'water'])->register();

        $this->assertTrue(post_type_supports('bread', 'flour'));
        $this->assertTrue(post_type_supports('bread', 'water'));

        Builder::make('butter')->supports('bread', 'spreading')->register();

        $this->assertTrue(post_type_supports('butter', 'bread'));
        $this->assertTrue(post_type_supports('butter', 'spreading'));
    }

    /**
     * @test
     */
    function it_can_get_and_set_arbitrary_values_for_the_registration_arguments()
    {
        $type = Builder::make('stuff')
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
        $public = Builder::make('a-public-type')->open();
        $this->assertTrue($public->get('public'));

        $private = Builder::make('a-private-type')->closed();
        $this->assertFalse($private->get('public'));
    }

    /**
     * @test
     */
    function it_has_dedicated_methods_for_user_interface()
    {
        $ui = Builder::make('ui-having')->withUI();
        $this->assertTrue($ui->get('show_ui'));

        $no_ui = Builder::make('no-ui')->noUI();
        $this->assertFalse($no_ui->get('show_ui'));
    }

    /**
     * @test
     */
    function it_has_methods_for_setting_the_labels()
    {
        $book = Builder::make('book')
            // override a default value
            ->setLabel('archives', 'All the Bookz')
            // override a default with a new placeholder
            ->setLabel('search_items', 'Find {many}')
            // set a non-standard label
            ->setLabel('some_custom_label', 'BOOKMADNESS')
            // populate singular defaults
            ->oneIs('Book')
            // populate plural defaults
            ->manyAre('Books')
            ->register();

        $labels = get_post_type_labels($book->object());

        $this->assertSame('Book', $labels->singular_name);
        $this->assertSame('Books', $labels->name);
        $this->assertSame('All Books', $labels->all_items);
        $this->assertSame('Edit Book', $labels->edit_item);
        $this->assertSame('Find Books', $labels->search_items);
        $this->assertSame('BOOKMADNESS', $labels->some_custom_label);
        $this->assertSame('Add New Book', $labels->add_new_item);
        $this->assertSame('All the Bookz', $labels->archives);
    }
}
