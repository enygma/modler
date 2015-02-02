<?php
namespace Modler;

class OtherModel extends \Modler\Model
{
    protected $properties = array(
        'test' => array(
            'description' => 'Test Property'
        )
    );

    public function callMeMaybe($test)
    {
        $this->setValue('test', 'foobarbaz - '.$test);
    }

    public function callMeReturnValue($test)
    {
        return 'this is a value: '.$test;
    }
}
