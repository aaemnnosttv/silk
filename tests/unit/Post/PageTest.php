<?php

use Silk\WordPress\Post\Page;

class PageTest extends WP_UnitTestCase
{
    /**
     * @test
     **/
    function it_works()
    {
        $page = $this->factory->post->create_and_get(['post_type' => 'page']);

        $model_from_id = Page::fromID($page->ID);
        $model_from_obj = Page::fromWpPost($page);
        $model_from_slug = Page::fromSlug($page->post_name);

        $this->assertSame($page->ID, $model_from_id->id);
        $this->assertSame($page->ID, $model_from_obj->id);
        $this->assertSame($page->ID, $model_from_slug->id);
    }

    /**
     * @test
     */
    function it_can_create_a_page_from_a_new_instance()
    {
        $model = new Page;
        $model->post_title = 'some title';
        $model->save();

        $this->assertGreaterThan(0, $model->id);

        $this->assertSame('page', $model->post_type);
    }

    /**
     * @test
     * @expectedException \Silk\Post\Exception\ModelPostTypeMismatchException
     */
    function it_blows_up_if_instantiated_with_a_non_page_post_type()
    {
        $post_id = $this->factory->post->create(['post_type' => 'post']);

        // this will blow up since the post id is for a post_type of `post`
        Page::fromID($post_id);
    }

}
