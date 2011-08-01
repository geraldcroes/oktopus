<?php
namespace Oktopus\tests\units;

require __DIR__.'/../bootstrap.php';

use \mageekguy\atoum;
use \Oktopus\Container;

class ComponentReference extends atoum\test
{
    public function test__construct ()
    {
        $componentReference = new \Oktopus\ComponentReference('id');
        $this->assert
            ->string($componentReference->getId())
                ->isEqualTo('id')
            ->variable($componentReference->getContainer())
                ->isNull();
    }

    public function testContainer ()
    {
        $container = new \Oktopus\Container();
        $componentReference = new \Oktopus\ComponentReference('id', $container);
        $this->assert
            ->string($componentReference->getId())
                ->isEqualTo('id')
            ->object($componentReference->getContainer())
                ->isIdenticalTo($container);
    }
}