<?php
namespace Oktopus\Di\Container\tests\units;

require_once __DIR__ . '/../../../bootstrap.php';

use \mageekguy\atoum;
use \Oktopus;

Oktopus\Engine::autoloader()->addPath(__DIR__ . '/../../../resources/container/');

class ContainerCollection extends atoum\test
{
    public function test__construct ()
    {
        $this->assert
                ->object($tested = new \Oktopus\Di\Container\ContainerCollection());
    }

    public function testHasComponent ()
    {
        $tested = new \Oktopus\Di\Container\ContainerCollection();

        $basicContainer = new \Oktopus\Di\Container\BasicContainer();
        $basicContainer->define('foo', '\Oktopus\Di\Container\tests\units\foo');

        $this->assert
                ->boolean($tested->hasComponent('foo'))
                    ->isFalse()//the collection does not have a component foo...
                ->object($tested->add($basicContainer))
                    ->isIdenticalTo($tested)
                ->boolean($tested->hasComponent('foo'))
                    ->isTrue()//until a basic container that contains this component is added to the collection
                ->object($tested->remove($basicContainer))
                    ->isIdenticalTo($tested)
                ->boolean($tested->hasComponent('foo'))
                    ->isFalse()//But if removed will start saying no one more time
                ->object($tested->add($basicContainer))
                    ->isIdenticalTo($tested)
                ->boolean($tested->hasComponent('foo'))
                    ->isTrue()
                ->boolean($tested->hasComponent('foo2'))
                    ->isFalse();

        $basicContainer2 = new \Oktopus\Di\Container\BasicContainer();
        $basicContainer2->define('foo2', '\Oktopus\Di\Container\tests\units\foo2');

        $this->assert
                ->object($tested->add($basicContainer2))
                    ->isIdenticalTo($tested)
                ->boolean($tested->hasComponent('foo2'))//now have foo2, thx to the addition of basicContainer2
                    ->isTrue()
                ->boolean($tested->hasComponent('foo'))//and still have foo (not lost)
                    ->isTrue()
                ->object($tested->remove($basicContainer))
                    ->isIdenticalTo($tested)
                ->boolean($tested->hasComponent('foo'))
                    ->isFalse()
                ->boolean($tested->hasComponent('foo2'))
                    ->isTrue();
    }

    public function testGet ()
    {
        $tested = new \Oktopus\Di\Container\ContainerCollection();

        $basicContainer = new \Oktopus\Di\Container\BasicContainer();
        $basicContainer->define('foo', '\Oktopus\Di\Container\tests\units\foo');

        $this->assert
            ->boolean($tested->hasComponent('foo'))
                ->isFalse()//the collection does not have a component foo...
            ->exception(function () use ($tested){
                $tested->get('foo');
            })
                ->isInstanceOf('Oktopus\Di\Container\ContainerException')
            ->object($tested->add($basicContainer))
                ->isIdenticalTo($tested)
            ->boolean($tested->hasComponent('foo'))
                ->isTrue()//until a basic container that contains this component is added to the collection
            ->object($instanceOfFoo = $tested->get('foo'))
                ->isInstanceOf('\Oktopus\Di\Container\tests\units\foo')
            ->object($tested->remove($basicContainer))
                ->isIdenticalTo($tested)
            ->boolean($tested->hasComponent('foo'))
                ->isFalse()//But if removed will start saying no one more time
            ->exception(function () use ($tested){
                $tested->get('foo');
            })
                ->isInstanceOf('Oktopus\Di\Container\ContainerException')
            ->object($tested->add($basicContainer))
                ->isIdenticalTo($tested)
            ->boolean($tested->hasComponent('foo'))
                ->isTrue()
            ->object($tested->get('foo'))
                ->isInstanceOf('\Oktopus\Di\Container\tests\units\foo')
                ->isIdenticalTo($instanceOfFoo)
            ->boolean($tested->hasComponent('foo2'))
                ->isFalse();

        $basicContainer2 = new \Oktopus\Di\Container\BasicContainer();
        $basicContainer2->define('foo2', '\Oktopus\Di\Container\tests\units\foo2');
        $basicContainer2->define('foo', '\Oktopus\Di\Container\tests\units\foo2');//registering another foo as a foo2 class

        $this->assert
            ->object($tested->add($basicContainer2))
                ->isIdenticalTo($tested)
            ->boolean($tested->hasComponent('foo2'))
                ->isTrue()
            ->object($tested->get('foo2'))
                ->isInstanceOf('\Oktopus\Di\Container\tests\units\foo2')
            ->boolean($tested->hasComponent('foo'))
                ->isTrue()
            ->object($tested->get('foo'))
                ->isInstanceOf('\Oktopus\Di\Container\tests\units\foo')//Foo is still the Foo of the first registered container.
                ->isIdenticalTo($instanceOfFoo)
            ->object($tested->remove($basicContainer))
                ->isIdenticalTo($tested)
            ->boolean($tested->hasComponent('foo'))
                ->isTrue()
            ->object($instanceOfFooFromBasicContainer2 = $tested->get('foo2'))
                ->isInstanceOf('\Oktopus\Di\Container\tests\units\foo2')//Now that basicContainer is not in the collection, basicContainer2 delivers its own component for foo
            ->object($tested->add($basicContainer))
            ->object($tested->get('foo2'))
                ->isInstanceOf('\Oktopus\Di\Container\tests\units\foo2');//Still foo2 as now the basicContainer is behind basicContainer2 in term of priority
    }
}

class Foo
{
}
class Foo2
{
}