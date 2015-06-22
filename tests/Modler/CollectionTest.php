<?php

namespace Modler;

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
     * Test the iteration of the collection
     */
    public function testIterateCollection()
    {
        $this->collection->add('foo');
        $this->collection->add('bar');

        foreach ($this->collection as $index => $value) {
            $this->assertTrue(in_array($index, array(0, 1)));
        }
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

        $count = array();
        foreach ($this->collection as $value) {
            $count[] = $value;
        }
        $this->assertEquals(2, count($count));
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

    /**
     * Test the slicing of the collection data
     */
    public function testSliceCollection()
    {
        $this->collection->add('foo');
        $this->collection->add('bar');
        $this->collection->add('baz');
        $this->collection->add('test');

        $this->assertEquals(
            $this->collection->slice(1),
            array('bar', 'baz', 'test')
        );
        $this->assertEquals(
            $this->collection->slice(2, 1),
            array('baz')
        );
    }

    /**
     * Test the "contains" checking for the collections
     */
    public function testCollectionContains()
    {
        $this->collection->add('foo');
        $this->collection->add('bar');

        $this->assertTrue($this->collection->contains('bar'));
    }

    /**
     * Test that the collection does not contain the value
     */
    public function testCollectionDoesNotContain()
    {
        $this->collection->add('foo');
        $this->collection->add('bar');

        $this->assertFalse($this->collection->contains('baz'));
    }

    /**
     * Test the "take" method on a collection
     */
    public function testLimitWithTake()
    {
        $data = array('foo', 'bar', 'baz', 'quux', 'foobar', 'barbaz');
        foreach ($data as $value) {
            $this->collection->add($value);
        }

        $collection = $this->collection->take(3);

        $this->assertEquals(3, count($collection));
        $this->assertEquals(
            array('foo', 'bar', 'baz'),
            $collection->toArray()
        );
    }

    /**
     * Test the default sorting (as string), descending
     */
    public function testOrderStringDataDefaultSort()
    {
        $data = array('foo', 'bar', 'quux', 'baz', 'foobar', 'barbaz');
        foreach ($data as $value) {
            $this->collection->add($value);
        }

        $collection = $this->collection->order();

        $this->assertEquals(
            array('bar', 'barbaz', 'baz', 'foo', 'foobar', 'quux'),
            $collection->toArray()
        );
    }

    /**
     * Test the order by a property with the default sort (well, DESC is default)
     */
    public function testOrderObjectPropertyDefaultSort()
    {
        $data = array('foo', 'bar', 'quux', 'baz', 'foobar', 'barbaz');
        foreach ($data as $value) {
            $object = new \stdClass();
            $object->test = $value;
            $this->collection->add($object);
        }

        $collection = $this->collection->order(TestCollection::SORT_DESC, 'test');

        $this->assertEquals(6, count($collection));
        $this->assertEquals('bar', $collection[0]->test);
        $this->assertEquals('foo', $collection[3]->test);
        $this->assertEquals('quux', $collection[5]->test);
    }

    /**
     * Test the "find" when the collection contains Modler models
     */
    public function testFindModlerModels()
    {
        for ($i = 1; $i <= 10; $i++) {
            $model = new TestModel(['id' => $i]);
            $this->collection->add($model);
        }
        $result = $this->collection->find('id', 2);
        $this->assertEquals(2, $result->id);
    }

    /**
     * Test the "find" when the collection contains regular objects
     */
    public function testFndSimpleObject()
    {
        for ($i = 1; $i <= 10; $i++) {
            $model = new \stdClass();
            $model->id = $i;
            $this->collection->add($model);
        }
        $result = $this->collection->find('id', 2);
        $this->assertEquals(2, $result->id);
    }

    /**
     * Test the "find" when the collection contains arrays
     */
    public function testFindArrays()
    {
        for ($i = 1; $i <= 10; $i++) {
            $item = array('id' => $i);
            $this->collection->add($item);
        }
        $result = $this->collection->find('id', 2);
        $this->assertEquals(2, $result['id']);
    }

    /**
     * Test the "find" when the collection contains just values
     */
    public function testFindJustValues()
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->collection->add($i);
        }
        $result = $this->collection->find('id', 2);
        $this->assertEquals(2, $result);
    }

    /**
     * Test the "find" when the "all" option is given
     *     telling it to return a set not just the first match
     */
    public function testFindMatchAll()
    {
        $this->collection->add(new TestModel(['id' => 2]));
        $this->collection->add(new TestModel(['id' => 1]));
        $this->collection->add(new TestModel(['id' => 2]));

        $result = $this->collection->find('id', 2, true);
        $this->assertEquals(2, count($result));
    }
}
