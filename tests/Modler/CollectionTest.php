<?php

namespace Modler;

require_once __DIR__.'/../TestCollection.php';
require_once __DIR__.'/../TestModel.php';

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    private $collection;

    public function setUp()
    {
        $this->collection = new TestCollection();
    }
    public function tearDown()
    {
        unset($this->collection);
    }

    /**
     * Test that, by default, a collection is empty (count)
     */
    public function testCollectionEmpty()
    {
        $this->assertEquals(count($this->collection), 0);
    }

    /**
     * Test that an item is added to the collection correctly
     */
    public function testAddToCollection()
    {
        $data = array('foo' => 'bar');
        $this->collection->add($data);

        $this->assertEquals(count($this->collection), 1);
    }

    /**
     * Test the return of a collection values as an array
     */
    public function testCollectionToArray()
    {
        $data = array('foo' => 'bar');
        $this->collection->add($data);

        $this->assertEquals(
            array($data),
            $this->collection->toArray()
        );
    }

    /**
     * Test the output of the toArray when one value is an object
     *     and another is a string
     */
    public function testCollectionToArrayMixed()
    {
        $model = new TestModel();
        $model->test = 'foobarbaz';
        $this->collection->add($model);

        $data = array('foo' => 'bar');
        $this->collection->add($data);

        $this->assertEquals(
            array(
                array('test' => 'foobarbaz'),
                $data
            ),
            $this->collection->toArray(true)
        );
    }

    /**
     * Test the expansion of the inner models when toArray
     *     is called on a collection with expand = true
     */
    public function testCollectionToArrayExpanded()
    {
        $model = new TestModel();
        $model->test = 'foobarbaz';

        $this->collection->add($model);

        $this->assertEquals(
            $this->collection->toArray(true),
            array(array('test' => 'foobarbaz'))
        );
    }

    /**
     * Test that a collection can be iterated over
     */
    public function testCollectionIteratable()
    {
        $this->collection->add(array('foo' => 'bar'));
        $this->collection->add(array('baz' => 'test'));

        $count = 0;
        foreach ($this->collection as $index => $value) {
            $count++;
        }
        $this->assertEquals(2, $count);
    }

    /**
     * Test the removal of an item from the collection
     */
    public function testRemoveFromCollection()
    {
        $this->collection->add(array('foo' => 'bar'));
        $this->collection->add(array('baz' => 'test'));

        $this->collection->remove(0);
        $this->assertEquals(
            array(1 => array('baz' => 'test')),
            $this->collection->toArray()
        );
    }

    /**
     * Test that the filter handling works as expected
     */
    public function testFilterByValue()
    {
        $this->collection->add(array('foo' => 'bar'));
        $this->collection->add(array('baz' => 'test'));

        $filtered = $this->collection->filter(function($value) {
            return (isset($value['foo']));
        });

        $this->assertEquals(
            $filtered->toArray(),
            array(array('foo' => 'bar'))
        );
    }
}