<?php

trait TermFactoryHelpers
{
    protected function createManyTagsForPost($count, $post_id)
    {
        $tags = $this->createManyTags($count);
        $this->factory->term->add_post_terms($post_id, $tags, 'post_tag');
    }

    protected function createManyCatsForPost($count, $post_id)
    {
        $tags = $this->createManyCats($count);
        $this->factory->term->add_post_terms($post_id, $tags, 'category');
    }

    protected function createManyTags($count)
    {
        return $this->factory->term->create_many($count, ['taxonomy' => 'post_tag']);
    }

    protected function createManyCats($count)
    {
        return $this->factory->term->create_many($count, ['taxonomy' => 'category']);
    }
}
