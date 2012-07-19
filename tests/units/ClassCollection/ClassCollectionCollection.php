<?php
namespace Oktopus\ClassCollection\tests\units;

require_once __DIR__ . '/../../bootstrap.php';

use \mageekguy\atoum;

class ClassCollectionCollection extends atoum\test
{
    public function test__construct ()
    {
        $arrayClassCollection = new \Oktopus\ClassCollection\ClassCollectionCollection();
    }

    public function testGetPath ()
    {
        $arrayClassCollection  = new \Oktopus\ClassCollection\ArrayClassCollection(array('first'=>'file1'));
        $arrayClassCollection2 = new \Oktopus\ClassCollection\ArrayClassCollection(array('second'=>'file2'));
        $arrayClassCollection3 = new \Oktopus\ClassCollection\ArrayClassCollection(array('first'=>'firstfile'));
        $collection = new \Oktopus\ClassCollection\ClassCollectionCollection();
        
        $this->assert
                 ->object($collection->add($arrayClassCollection))
                     ->isIdenticalTo($collection)
                 ->object($collection->add($arrayClassCollection2))
                     ->isIdenticalTo($collection);
		
        $this->assert
                ->variable($collection->getPath('foo'))
                    ->isNull();

        $this->assert
                ->string($collection->getPath('first'))
                    ->isEqualTo('file1')
                ->string($collection->getPath('second'))
                    ->isEqualTo('file2');

        $this->assert
                ->array($collection->getList())
                    ->isEqualTo(array('first'=>'file1', 'second'=>'file2'));
    }
}
