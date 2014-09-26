<?php

namespace Modler;

class TestModel extends \Modler\Model
{
    protected $properties = array(
        'test' => array(
            'description' => 'Test Property'
        ),
        'relateToMe' => array(
            'type' => 'relation',
            'relation' => array(
                'model' => '\\Modler\\OtherModel',
                'method' => 'callMeMaybe',
                'local' => 'test'
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