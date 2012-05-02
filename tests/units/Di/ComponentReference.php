<?php
namespace Oktopus\Di\tests\units;

require __DIR__.'/../../bootstrap.php';

use \mageekguy\atoum;
use \Oktopus\Container;

class ComponentReference extends atoum\test
{
    public function test__construct ()
    {
        $componentReference = new \Oktopus\Di\ComponentReference('id');
        $this->assert
            ->string($componentReference->getId())
                ->isEqualTo('id')
            ->variable($componentReference->getContainer())
                ->isNull();
    }

    public function testContainer ()
    {
        $container = new \Oktopus\Di\BasicContainer();
        $componentReference = new \Oktopus\Di\ComponentReference('id', $container);
        $this->assert
            ->string($componentReference->getId())
                ->isEqualTo('id')
            ->object($componentReference->getContainer())
                ->isIdenticalTo($container);
    }
}