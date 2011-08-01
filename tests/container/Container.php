<?php
namespace Oktopus\tests\units {
    require __DIR__.'/../bootstrap.php';

    use \mageekguy\atoum;
    use \Oktopus;

    interface MockFoo
    {
        public function fooDirect();
        public function setFoo();
        public function setFoo2();
        public function firstParameter();
        public function secondParameter();
    }

    interface MockFooForTest
    {
        public function getValue1();
    }

    class Container extends atoum\test
    {
        public function testBasicConstruction ()
        {
            //Testing nested call in definition
            $container = new \Oktopus\Container();
            $container->define('foo', 'foodi')
                      ->setProperty('_fooDirect', '_fooDirect')
                      ->setMethod('setFoo', array('foo'))
                      ->setMethod('setFoo2', array('foo2'))
                      ->setConstructorArguments(array('foo1', 'foo2'));
            $this->assert->boolean($container->hasComponent('foo'))->isTrue();
            $this->assert->boolean($container->hasComponent('foodi'))->isFalse();

            $foo = $container->get('foo');

            $this->assert->string($foo->getFooDirect())->isEqualTo('_fooDirect');
            $this->assert->string($foo->getFoo())->isEqualTo('foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('foo2');
            $this->assert->string($foo->getFirstParameter())->isEqualTo('foo1');
            $this->assert->string($foo->getSecondParameter())->isEqualTo('foo2');

            //Testing multiple call of define in definition to assert it will add
            // element to the existing one
            $container = new \Oktopus\Container();
            $container->define('foo')
                      ->setClass('foodi');
            $container->getDefinition('foo')
                      ->setProperty('_fooDirect', '_fooDirect')
                      ->setMethod('setFoo', array('foo'));
            $container->getDefinition('foo')
                      ->setMethod('setFoo2', array('foo2'))
                      ->setConstructorArguments(array('foo1', 'foo2'));
            $foo = $container->get('foo');

            $this->assert->string($foo->getFooDirect())->isEqualTo('_fooDirect');
            $this->assert->string($foo->getFoo())->isEqualTo('foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('foo2');
            $this->assert->string($foo->getFirstParameter())->isEqualTo('foo1');
            $this->assert->string($foo->getSecondParameter())->isEqualTo('foo2');
        }

        public function testDoubleDefinition ()
        {
            $container = new Oktopus\Container();
            $container->define('foo')
                      ->setClass('foodi');
            //Trying to define two objects with the same id should raise an error
            $this->assert->exception(function()use($container){$container->define('foo');})->isInstanceOf('Oktopus\ContainerException');
        }

        public function testLazyConstruction ()
        {
            $this->mockGenerator->generate('\Oktopus\tests\units\MockFoo');
            $mockFoo = new \mock\Oktopus\tests\units\MockFoo;

            $container = new Oktopus\Container();
            $container->define('foo', 'foodi')
                      ->setProperty('_fooDirect', function () use($mockFoo) {
                          $mockFoo->fooDirect();
                          return '_fooDirect';
                      })
                      ->setMethod('setFoo', array(function() use($mockFoo){
                        $mockFoo->setFoo();
                        return 'foo';
                      }))
                      ->setMethod('setFoo2', array(function() use($mockFoo){
                        $mockFoo->setFoo2();
                        return 'foo2';
                      }))
                      ->setConstructorArguments(array(
                         function() use($mockFoo){
                            $mockFoo->firstParameter();
                            return 'foo1';
                         },
                         function() use($mockFoo){
                            $mockFoo->secondParameter();
                            return 'foo2';
                         }));

            $this->assert->mock($mockFoo)
                            ->call('fooDirect')->never()
                            ->call('setFoo')->never()
                            ->call('setFoo2')->never()
                            ->call('firstParameter')->never()
                            ->call('secondParameter')->never()
                         ->object($foo = $container->get('foo'))
                            ->mock($mockFoo)
                            ->call('fooDirect')->once()
                            ->call('setFoo')->once()
                            ->call('setFoo2')->once()
                            ->call('firstParameter')->once()
                            ->call('secondParameter')->once()
                         ->object($foo2 = $container->get('foo'))
                            ->mock($mockFoo)
                            ->call('fooDirect')->once()
                            ->call('setFoo')->once()
                            ->call('setFoo2')->once()
                            ->call('firstParameter')->once()
                            ->call('secondParameter')->once();

            $this->assert->string($foo->getFooDirect())->isEqualTo('_fooDirect');
            $this->assert->string($foo->getFoo())->isEqualTo('foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('foo2');
            $this->assert->string($foo->getFirstParameter())->isEqualTo('foo1');
            $this->assert->string($foo->getSecondParameter())->isEqualTo('foo2');

            $this->assert->string($foo2->getFooDirect())->isEqualTo('_fooDirect');
            $this->assert->string($foo2->getFoo())->isEqualTo('foo');
            $this->assert->string($foo2->getFoo2())->isEqualTo('foo2');
            $this->assert->string($foo2->getFirstParameter())->isEqualTo('foo1');
            $this->assert->string($foo2->getSecondParameter())->isEqualTo('foo2');

            $this->assert->object($foo)->isIdenticalTo($foo);


            //Now testing with an unshared component
            $mockFoo2 = new \mock\Oktopus\tests\units\MockFoo;

            $container = new Oktopus\Container();
            $container->define('foo2', 'foodi')
                      ->setProperty('_fooDirect', function () use($mockFoo2) {
                          $mockFoo2->fooDirect();
                          return '_fooDirect';
                      })
                      ->setMethod('setFoo', array(function() use($mockFoo2){
                        $mockFoo2->setFoo();
                        return 'foo';
                      }))
                      ->setMethod('setFoo2', array(function() use($mockFoo2){
                        $mockFoo2->setFoo2();
                        return 'foo2';
                      }))
                      ->setConstructorArguments(array(
                         function() use($mockFoo2){
                            $mockFoo2->firstParameter();
                            return 'foo1';
                         },
                         function() use($mockFoo2){
                            $mockFoo2->secondParameter();
                            return 'foo2';
                         }))
                      ->setShared(false);

            $this->assert->mock($mockFoo2)
                            ->call('fooDirect')->never()
                            ->call('setFoo')->never()
                            ->call('setFoo2')->never()
                            ->call('firstParameter')->never()
                            ->call('secondParameter')->never()
                         ->object($foo = $container->get('foo2'))
                            ->mock($mockFoo2)
                            ->call('fooDirect')->once()
                            ->call('setFoo')->once()
                            ->call('setFoo2')->once()
                            ->call('firstParameter')->once()
                            ->call('secondParameter')->once()
                         ->object($foo2 = $container->get('foo2'))
                            ->mock($mockFoo2)
                            ->call('fooDirect')->exactly(2)
                            ->call('setFoo')->exactly(2)
                            ->call('setFoo2')->exactly(2)
                            ->call('firstParameter')->exactly(2)
                            ->call('secondParameter')->exactly(2);

            $this->assert->string($foo->getFooDirect())->isEqualTo('_fooDirect');
            $this->assert->string($foo->getFoo())->isEqualTo('foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('foo2');
            $this->assert->string($foo->getFirstParameter())->isEqualTo('foo1');
            $this->assert->string($foo->getSecondParameter())->isEqualTo('foo2');

            $this->assert->string($foo2->getFooDirect())->isEqualTo('_fooDirect');
            $this->assert->string($foo2->getFoo())->isEqualTo('foo');
            $this->assert->string($foo2->getFoo2())->isEqualTo('foo2');
            $this->assert->string($foo2->getFirstParameter())->isEqualTo('foo1');
            $this->assert->string($foo2->getSecondParameter())->isEqualTo('foo2');

            $this->assert->object($foo)->isIdenticalTo($foo);
        }

        public function testGettingNotSet ()
        {
            $container = new \Oktopus\Container();
            $this->assert->exception(function () use($container) {$container->get('unset');})->isInstanceOf('\Oktopus\ContainerException');
        }

        public function testShared ()
        {
            //Shared is set to true, container will distribute the same component
            $container = new Oktopus\Container();
            $container->define('foodi')
                      ->setConstructorArguments(array('foo1', 'foo2'))
                      ->setShared(true);
            $foo  = $container->get('foodi');
            $foo2 = $container->get('foodi');
            $this->assert->object($foo)->isInstanceOf('foodi')->isIdenticalTo($foo2);

            //Shared is set to false, container will NOT distribute the same component
            $container = new Oktopus\Container();
            $container->define('foo', 'foodi')
                      ->setConstructorArguments(array('foo1', 'foo2'))
                      ->setShared(false);
            $foo  = $container->get('foo');
            $foo2 = $container->get('foo');

            $this->assert->object($foo)->isInstanceOf('foodi')->isNotIdenticalTo($foo2);
            $this->assert->object($foo2)->isInstanceOf('foodi')->isNotIdenticalTo($foo);
            $this->assert->object($foo)->isEqualTo($foo2);
        }

        public function testEmptyConstructorAndMethodCalls ()
        {
            //Setting no parameters to the constructor
            $container = new Oktopus\Container();
            $container->define('foo', 'foodi2')
                      ->setMethod('setFoo')
                      ->setConstructorArguments();
            $foo = $container->get('foo');

            $this->assert->string($foo->getFoo())->isEqualTo('value of foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('value of foo2');

            //Setting no parameters to the constructor
            $container = new Oktopus\Container();
            $container->define('foodi2')
                      ->setMethod('setFoo');
            $foo = $container->get('foodi2');

            $this->assert->string($foo->getFoo())->isEqualTo('value of foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('value of foo2');
        }

        public function testExternalFactory ()
        {
            //With no parameters
            $container = new Oktopus\Container();
            $container->define('foodi2')
                      ->setMethod('setFoo')
                      ->setFactory(array('FooDi2Factory', 'getInstance'));
            $foo = $container->get('foodi2');

            $this->assert->string($foo->getFoo())->isEqualTo('value of foo');
            $this->assert->string($foo->getFoo2())->isEqualTo('value of foo2');

            //With parameters
            $container = new Oktopus\Container();
            $container->define('foodi')
                      ->setMethod('setFoo', array('value'))
                      ->setFactory(array('FooDiFactory', 'getInstance'), array('value1', 'value2'));
            $foo = $container->get('foodi');

            $this->assert->string($foo->getFoo())->isEqualTo('value');
            $this->assert->string($foo->getFirstParameter())->isEqualTo('value1');
            $this->assert->string($foo->getSecondParameter())->isEqualTo('value2');

            //With lazy parameters
            $this->mockGenerator->generate('MockFooForTest');
            $mock = new \mock\MockFooForTest();
            $mock->getMockController()->getValue1 = 'value1';

            $container = new Oktopus\Container();
            $container->define('foodi')
                      ->setMethod('setFoo', array('value'))
                      ->setFactory(
                                   array('FooDiFactory', 'getInstance'),
                                   array
                                     (
                                        function() use($mock){
                                            return $mock->getValue1();
                                        },
                                        'value2'
                                     )
                                  );

            $this->assert->mock($mock)
                            ->call('getValue1')->never()
                         ->object($foo = $container->get('foodi'))
                            ->mock($mock)
                            ->call('getValue1')->once();

            $this->assert->string($foo->getFoo())->isEqualTo('value');
            $this->assert->string($foo->getFirstParameter())->isEqualTo('value1');
            $this->assert->string($foo->getSecondParameter())->isEqualTo('value2');
        }
        public function testWithSharedComponent ()
           {
               $container = new \Oktopus\Container();

               //Setting a shared instance
               $container->define('UniqueInstance', '\Oktopus\tests\units\UniqueInstance')
                         ->setShared(true);

               $ui  = $container->get('UniqueInstance');
               $ui2 = $container->get('UniqueInstance');
               $this->assert->object($ui)->isIdenticalTo($ui2);

               //testing with a single reference in the same container
               $container->define('UseUniqueInstance','\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance'))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance')))
               ->setConstructorArguments(array(new \Oktopus\ComponentReference('UniqueInstance')));

               $useUniqueInstance = $container->get('UseUniqueInstance');
               $this->assert->object($useUniqueInstance->getUniqueInstance())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance->getUniqueInstance2())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance->getUniqueInstance3())->isIdenticalTo($ui);

               //Testing with an a reference in another container
               $container2 = new \Oktopus\Container();
               $container2->define('UseUniqueInstance','\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance', $container))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance', $container)))
               ->setConstructorArguments(array(new \Oktopus\ComponentReference('UniqueInstance', $container)));

               $useUniqueInstance2 = $container2->get('UseUniqueInstance');
               $this->assert->object($useUniqueInstance2->getUniqueInstance())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance2->getUniqueInstance2())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance2->getUniqueInstance3())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance)->IsNotIdenticalTo($useUniqueInstance2);

               //Test using a factory and using unique instance in the same container
               $container->define('UseUniqueInstance2', '\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance'))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance')))
               ->setFactory(array('\Oktopus\tests\units\UseUniqueInstanceFactory', 'create'), array(new \Oktopus\ComponentReference('UniqueInstance')));

               $useUniqueInstance3 = $container->get('UseUniqueInstance2');
               $this->assert->object($ui)->isIdenticalTo($useUniqueInstance3->getUniqueInstance());
               $this->assert->object($ui)->isIdenticalTo($useUniqueInstance3->getUniqueInstance2());
               $this->assert->object($ui)->isIdenticalTo($useUniqueInstance3->getUniqueInstance3());
               $this->assert->object($useUniqueInstance)->isNotIdenticalTo($useUniqueInstance3);

               //test using a factory and using unique instance in another container
               $container2->define('UseUniqueInstance2', '\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance', $container))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance', $container)))
               ->setFactory(array('\Oktopus\tests\units\UseUniqueInstanceFactory', 'create'), array(new \Oktopus\ComponentReference('UniqueInstance', $container)));

               $useUniqueInstance4 = $container2->get('UseUniqueInstance2');
               $this->assert->object($useUniqueInstance4->getUniqueInstance())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance4->getUniqueInstance2())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance4->getUniqueInstance3())->isIdenticalTo($ui);
               $this->assert->object($useUniqueInstance4)->isNotIdenticalTo($useUniqueInstance3);
           }

           public function testWithUnSharedComponent ()
           {
               $container = new \Oktopus\Container();

               //Setting a shared instance
               $container->define('UniqueInstance', '\Oktopus\tests\units\UniqueInstance')
                         ->setShared(false);

               $ui  = $container->get('UniqueInstance');
               $ui2 = $container->get('UniqueInstance');
               $this->assert->object($ui)->isNotIdenticalTo($ui2);

               //testing with a single reference in the same container
               $container->define('UseUniqueInstance', '\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance'))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance')))
               ->setConstructorArguments(array(new \Oktopus\ComponentReference('UniqueInstance')));

               $useUniqueInstance = $container->get('UseUniqueInstance');
               $this->assert->object($useUniqueInstance->getUniqueInstance())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance->getUniqueInstance2())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance->getUniqueInstance3())->isNotIdenticalTo($ui);

               //Testing with an a reference in another container
               $container2 = new \Oktopus\Container();
               $container2->define('UseUniqueInstance', '\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance', $container))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance', $container)))
               ->setConstructorArguments(array(new \Oktopus\ComponentReference('UniqueInstance', $container)));

               $useUniqueInstance2 = $container2->get('UseUniqueInstance');
               $this->assert->object($useUniqueInstance2->getUniqueInstance())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance2->getUniqueInstance2())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance2->getUniqueInstance3())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance)->isNotIdenticalTo($useUniqueInstance2);

               //Test using a factory and using unique instance in the same container
               $container->define('UseUniqueInstance2', '\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance'))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance')))
               ->setFactory(array('\Oktopus\tests\units\UseUniqueInstanceFactory', 'create'), array(new \Oktopus\ComponentReference('UniqueInstance')));

               $useUniqueInstance3 = $container->get('UseUniqueInstance2');
               $this->assert->object($useUniqueInstance3->getUniqueInstance())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance3->getUniqueInstance2())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance3->getUniqueInstance3())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance)->isNotIdenticalTo($useUniqueInstance3);

               //test using a factory and using unique instance in another container
               $container2->define('UseUniqueInstance2', '\Oktopus\tests\units\UseUniqueInstance')
               ->setProperty('_uniqueInstance3', new \Oktopus\ComponentReference('UniqueInstance', $container))
               ->setMethod('setUniqueInstance2', array(new \Oktopus\ComponentReference('UniqueInstance', $container)))
               ->setFactory(array('\Oktopus\tests\units\UseUniqueInstanceFactory', 'create'), array(new \Oktopus\ComponentReference('UniqueInstance', $container)));

               $useUniqueInstance4 = $container2->get('UseUniqueInstance2');
               $this->assert->object($useUniqueInstance4->getUniqueInstance())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance4->getUniqueInstance2())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance4->getUniqueInstance3())->isNotIdenticalTo($ui);
               $this->assert->object($useUniqueInstance4)->isNotIdenticalTo($useUniqueInstance3);
           }
    }

    class UseUniqueInstance
    {
        private $_uniqueInstance3;
        private $_uniqueInstance2;
        private $_uniqueInstance;

        public function setUniqueInstance2 ($pUniqueInstance)
        {
            $this->_uniqueInstance2 = $pUniqueInstance;
        }

        public function __construct ($pUniqueInstance)
        {
            $this->_uniqueInstance = $pUniqueInstance;
        }

        public function getUniqueInstance ()
        {
            return $this->_uniqueInstance;
        }

        public function getUniqueInstance2 ()
        {
            return $this->_uniqueInstance2;
        }

        public function getUniqueInstance3 ()
        {
            return $this->_uniqueInstance3;
        }
    }

    class UseUniqueInstanceFactory
    {
        public static function create ($pUniqueInstance)
        {
            return new UseUniqueInstance($pUniqueInstance);
        }
    }

    class UniqueInstance
    {
        private $_date;

        public function __construct ()
        {
            $this->_date = date('Y-m-d H:i:s');
        }

        public function getDate ()
        {
            return $this->_date;
        }
    }
}
namespace {
    class FooDI
    {
        private $_fooDirect;

        private $_foo = null;
        private $_foo2 = null;

        public function setFoo ($value)
        {
            $this->_foo = $value;
        }

        public function getFoo ()
        {
            return $this->_foo;
        }

        public function getFooDirect ()
        {
            return $this->_fooDirect;
        }
        public function setFoo2 ($value)
        {
            if ($this->getFoo() === null) {
                throw new Oktopus\Exception('setFoo should have been called before setFoo2');
            }
            $this->_foo2 = $value;
        }
        public function getFoo2 ()
        {
            return $this->_foo2;
        }

        private $_firstParameter;
        private $_secondParameter;
        public function __construct ($pFirstParameter, $pSecondParameter)
        {
            $this->_firstParameter = $pFirstParameter;
            $this->_secondParameter = $pSecondParameter;
        }
        public function getFirstParameter ()
        {
            return $this->_firstParameter;
        }
        public function getSecondParameter ()
        {
            return $this->_secondParameter;
        }
    }

    class FooDI2
    {
        private $_foo = null;
        private $_foo2 = null;

        public function setFoo ()
        {
            $this->_foo = 'value of foo';
        }

        public function getFoo ()
        {
            return $this->_foo;
        }

        public function getFoo2 ()
        {
            return $this->_foo2;
        }

        public function __construct ()
        {
            $this->_foo2 = 'value of foo2';
        }
    }

    class FooDI2Factory
    {
        public static function getInstance ()
        {
            return new FooDI2();
        }
    }

    class FooDIFactory
    {
        public static function getInstance($pParameter1, $pParameter2)
        {
            return new FooDI($pParameter1, $pParameter2);
        }
    }
}