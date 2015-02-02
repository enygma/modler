<?php

namespace Modler;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    private $model;

    public function setUp()
    {
        $this->model = new TestModel();
    }
    public function tearDown()
    {
        unset($this->model);
    }

    /**
     * Test that the data given at init is loaded
     */
    public function testLoadData()
    {
        $data = array('test' => 'foo');
        $model = new TestModel($data);

        $this->assertEquals($data, $model->toArray());
    }

    /**
     * Test that a property not known in the properties
     *     isn't set when loaded
     */
    public function testLoadUnknownProperty()
    {
        $this->model->load(array('foo' => 'bar'));
        $this->assertEmpty($this->model->toArray());
    }

    /**
     * Test the getter/setter for properties
     */
    public function testGetSetProperties()
    {
        $property = array(
            'description' => 'This is a test'
        );
        $this->model->addProperty('testing', $property);

        $this->assertTrue(
            array_key_exists('testing', $this->model->getProperties())
        );
        $this->assertEquals(
            $this->model->getProperty('testing'),
            $property
        );
    }

    /**
     * Test that an exception is thrown when you try to add
     *     a property that already exists
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetExistingProperty()
    {
        $this->model->addProperty('test', array(
            'description' => 'This is a duplicate'
        ));
    }

    /**
     * Test that a property can be overrideen with the
     *     extra property (replacing one already there)
     */
    public function testSetExistingPropertyOverride()
    {
        $this->model->addProperty('test', array(
            'description' => 'This is a duplicate'
        ), true);

        $properties = $this->model->getProperties();
        $this->assertEquals(
            $properties['test']['description'],
            'This is a duplicate'
        );
    }

    /**
     * Test the getter/setter for model values
     */
    public function testGetSetValues()
    {
        $value = 'test';
        $this->model->setValue('foo', $value);

        $this->assertEquals($this->model->getValue('foo'), $value);
    }

    /**
     * Test that the magic get/set methods are doing their job
     */
    public function testMagicGetSetProperty()
    {
        $value = 'testing123';
        $this->model->test = $value;

        $this->assertEquals($this->model->test, $value);
    }

    /**
     * Test that an exception is thrown when you try to __set
     *     a property that doesn't exist
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMagicSetInvalidProperty()
    {
        $this->model->foo = 'test';
    }

    /**
     * Test that an exception is thrown when an invalid property is requested
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMagicGetInvalidProperty()
    {
        echo $this->model->foobar;
    }

    /**
     * Test that the get* handling is working for property values
     */
    public function testMagicGetFunction()
    {
        $this->model->test = 'foo';
        $this->assertEquals('foo', $this->model->getTest());
    }

    /**
     * Test that the get* call on an invalid property returns null
     */
    public function testMagicGetFunctionInvalid()
    {
        $this->assertNull($this->model->getFoo());
    }

    /**
     * Test that the relationship from TestModel and OtherModel
     *     is correctly linked. If the link is correct, "callMeMaybe"
     *     is executed and the "test" value is set
     */
    public function testGetRelationValid()
    {
        $this->model->test = 'woo';
        $result = $this->model->relateToMe;

        $this->assertEquals(get_class($result), 'Modler\\OtherModel');
        $this->assertEquals($result->test, 'foobarbaz - woo');
    }

    /**
     * Test that the "return value" works correctly
     */
    public function testGetRelationReturnValue()
    {
        $this->model->test = 'woo';
        $result = $this->model->relateToMeValue;

        $this->assertEquals($result, 'this is a value: woo');
    }

    /**
     * Test that an exception is thrown when a bad model is named in
     *     the relationship
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetRelationInvalidModel()
    {
        $this->model->badModel;
    }

    /**
     * Test that an exception is thrown when a bad method is named in
     *     the relationship
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetRelationInvalidMethod()
    {
        $this->model->badMethod;
    }

    /**
     * Test that, when the required field is set, verification passes
     */
    public function testVerifyPass()
    {
        $this->model->imRequired = 'test';
        $this->model->verify();
    }

    /**
     * Test that when the required field is missing, an exception is thrown
     *
     * @expectedException \InvalidArgumentException
     */
    public function testVerifyFail()
    {
        $this->model->verify();
    }

    /**
     * Test the "ignore" property handling
     */
    public function testVerifyIgnorePass()
    {
        $ignore = array('imRequired');
        $this->model->verify($ignore);
    }
}
