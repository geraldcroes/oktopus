<?php
namespace Oktopus\ClassCollection\tests\units;

require_once __DIR__ . '/../../bootstrap.php';

use \mageekguy\atoum;

class ArrayClassCollection extends atoum\test
{
    public function test__construct ()
    {
        $arrayClassCollection = new \Oktopus\ClassCollection\ArrayClassCollection(array());
    }

    public function testGetPath ()
    {
        $arrayClassCollection = new \Oktopus\ClassCollection\ArrayClassCollection(array());
        $this->assert
                ->variable($arrayClassCollection->getPath('foo'))
                    ->isNull();

        $arrayClassCollection = new \Oktopus\ClassCollection\ArrayClassCollection(array('first'=>'file', 'second'=>'file2'));
        $this->assert
                ->string($arrayClassCollection->getPath('first'))
                    ->isEqualTo('file')
                ->string($arrayClassCollection->getPath('second'))
                    ->isEqualTo('file2');
    }
}