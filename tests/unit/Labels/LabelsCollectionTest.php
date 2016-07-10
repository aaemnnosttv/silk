<?php

use Silk\Labels\LabelsCollection;

class LabelsCollectionTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_takes_an_array_of_labels_and_replaces_placeholders_with_the_given_form()
    {
        $collection = new LabelsCollection([
            'greeting' => 'Hi %s'
        ]);
        $collection->setForm('there');

        $this->assertSame('Hi there', $collection->get('greeting'));

        $this->assertSame(['greeting' => 'Hi there'], $collection->toArray());
        $this->assertSame(['greeting' => 'Hi there'], $collection->replaced()->all());
    }

}
