<?php

trait PostTypeAssertions
{
    /**
     * [assertPostTypeExists description]
     * @param  [type] $slug [description]
     */
    protected function assertPostTypeExists($slug)
    {
        $this->assertTrue(post_type_exists($slug));
    }

    /**
     * [assertPostTypeExists description]
     * @param  [type] $slug [description]
     */
    protected function assertPostTypeNotExists($slug)
    {
        $this->assertFalse(post_type_exists($slug));
    }
}
