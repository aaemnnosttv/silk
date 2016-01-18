<?php

use Silk\Models\Page;
use Silk\Models\Exceptions\ModelPostTypeMismatchException;


class PageTest extends WP_UnitTestCase
{
    /**
     * @test
     **/
    function it_works()
    {
        $page_id = $this->factory->post->create(['post_type' => 'page']);

        Page::fromID($page_id);
    }

    /**
     * @test
     * @expectedException \Silk\Models\Exceptions\ModelPostTypeMismatchException
     */
    function it_blows_up_if_instantiated_with_a_non_page_post_type()
    {
        $post_id = $this->factory->post->create(['post_type' => 'post']);

        // this will blow up since the post id is for a post_type of `post`
        Page::fromID($post_id);
    }

}
