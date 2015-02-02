<?php
namespace Modler;

class TestModel extends \Modler\Model
{
    protected $properties = array(
        'test' => array(
            'description' => 'Test Property'
        ),
        'imRequired' => array(
            'description' => 'Required Property #1',
            'required' => true
        ),
        'relateToMe' => array(
            'type' => 'relation',
            'relation' => array(
                'model' => '\\Modler\\OtherModel',
                'method' => 'callMeMaybe',
                'local' => 'test'
            )
        ),
        'relateToMeValue' => array(
            'type' => 'relation',
            'relation' => array(
                'model' => '\\Modler\\OtherModel',
                'method' => 'callMeReturnValue',
                'local' => 'test',
                'return' => 'value'
            )
        ),
        'badModel' => array(
            'type' => 'relation',
            'relation' => array(
                'model' => '\\Foo\\Model',
                'method' => 'badMethod',
                'local' => 'badProperty'
            )
        ),
        'badMethod' => array(
            'type' => 'relation',
            'relation' => array(
                'model' => '\\Modler\\OtherModel',
                'method' => 'badMethod',
                'local' => 'badProperty'
            )
        ),
    );
}
