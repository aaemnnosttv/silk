<?php

use Silk\Post\Model;
use Silk\PostType\Builder;

class ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_has_a_method_for_getting_the_post_type_id()
    {
        $this->assertSame('event', ModelTestEvent::postTypeId());
        $this->assertSame('event', ModelTestEventTrait::postTypeId());
        $this->assertSame('model_test_post_type', ModelTestPostType::postTypeId());
        $this->assertSame('dinosaur', Dinosaur::postTypeId());

        $this->assertSame('event', ModelTestEvent::typeId());
        $this->assertSame('event', ModelTestEventTrait::typeId());
    }

    /**
     * @test
     */
    function it_has_a_method_for_getting_the_post_type_api()
    {
        $this->assertInstanceOf(Builder::class, Dinosaur::postType());
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
