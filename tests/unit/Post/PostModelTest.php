<?php

use Silk\Post\Model;
use Silk\PostType\Builder;

class PostModelTest extends WP_UnitTestCase
{
    /** @test */
    function it_has_a_method_for_getting_the_post_type_id()
    {
        $this->assertSame('event', ModelTestEvent::postTypeId());
        $this->assertSame('event', ModelTestEventTrait::postTypeId());
        $this->assertSame('model_test_post_type', ModelTestPostType::postTypeId());
        $this->assertSame('dinosaur', Dinosaur::postTypeId());
    }

    /** @test */
    function it_has_a_method_for_getting_the_post_type_api()
    {
        $this->assertInstanceOf(Builder::class, Dinosaur::postType());
    }

    /** @test */
    function it_has_a_named_constructor_to_make_a_new_instance()
    {
        $this->assertInstanceOf(Dinosaur::class, Dinosaur::make());
    }

    /** @test */
    function the_make_method_passes_its_arguments_to_the_constructor()
    {
        $wp_post = $this->factory()->post->create_and_get(['post_type' => 'event']);
        $model = ModelTestEvent::make($wp_post);

        $this->assertSame($wp_post, $model->object);
    }

    /** @test */
    function it_can_create_a_new_post_with_shorthand_attributes()
    {
        $model = ModelTestShorthand::create([
            'title'   => 'The Title',
            'name'    => 'urlish-title',
            'excerpt' => 'something cool',
            'post_content' => 'Some content', // required to test parent method
        ]);

        $post = get_post($model->id);

        $this->assertSame('The Title', $post->post_title);
        $this->assertSame('urlish-title', $post->post_name);
        $this->assertSame('something cool', $post->post_excerpt);
        $this->assertSame('Some content', $post->post_content);
    }

    /** @test */
    function it_has_models_for_all_builtin_post_types()
    {
        $this->assertSame('attachment'      , \Silk\WordPress\Post\Attachment::postTypeId());
        $this->assertSame('nav_menu_item'   , \Silk\WordPress\Post\NavMenuItem::postTypeId());
        $this->assertSame('page'            , \Silk\WordPress\Post\Page::postTypeId());
        $this->assertSame('post'            , \Silk\WordPress\Post\Post::postTypeId());
        $this->assertSame('revision'        , \Silk\WordPress\Post\Revision::postTypeId());
    }
}

/**
 * Models post with post_type 'event'
 */
class ModelTestEvent extends Model
{
    const POST_TYPE = 'event';
}

/**
 * Models post with post_type 'dinosaur'
 */
class Dinosaur extends Model
{
    use Silk\Post\ClassNameAsPostType;
}

/**
 * Models post with post_type 'model_test_post_type'
 */
class ModelTestPostType extends Model
{
    use Silk\Post\ClassNameAsPostType;
}

/**
 * Models post with post_type 'model_test_post_type'
 */
class ModelTestEventTrait extends ModelTestEvent
{
    /**
     * Here the trait is overriden by the constant
     */
    use Silk\Post\ClassNameAsPostType;
}

class ModelTestShorthand extends Model
{
    use \Silk\Type\ShorthandProperties;
}
