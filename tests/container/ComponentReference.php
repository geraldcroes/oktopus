<?php
namespace Oktopus\tests\units;

require __DIR__.'/../bootstrap.php';

use \mageekguy\atoum;
use \Oktopus\Container;

class ComponentReference extends atoum\test
{
    public function testWithSharedComponent ()
    {
        $container = new Container();
        
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
        $container2 = new Container();
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
        $container = new Container();

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
        $container2 = new Container();
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