<?php
use Oktopus\Container;
use Oktopus\ComponentReference;

class ComponentReferenceTest extends PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        require_once __DIR__.'/../bootstrap.php';
    }

    public function testWithSharedComponent ()
    {
        $container = new Container();
        
        //Setting a shared instance
        $container->define('UniqueInstance')
                  ->setShared(true);

        $ui  = $container->get('UniqueInstance');
        $ui2 = $container->get('UniqueInstance');
        $this->assertSame($ui, $ui2);
 
        //testing with a single reference in the same container
        $container->define('UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance'))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance')))
        ->setConstructorArguments(array(new ComponentReference('UniqueInstance')));
        
        $useUniqueInstance = $container->get('UseUniqueInstance');
        $this->assertSame($ui, $useUniqueInstance->getUniqueInstance());
        $this->assertSame($ui, $useUniqueInstance->getUniqueInstance2());
        $this->assertSame($ui, $useUniqueInstance->getUniqueInstance3());
        
        //Testing with an a reference in another container
        $container2 = new Container();
        $container2->define('UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance', $container))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance', $container)))
        ->setConstructorArguments(array(new ComponentReference('UniqueInstance', $container)));

        $useUniqueInstance2 = $container2->get('UseUniqueInstance');
        $this->assertSame($ui, $useUniqueInstance2->getUniqueInstance());
        $this->assertSame($ui, $useUniqueInstance2->getUniqueInstance2());
        $this->assertSame($ui, $useUniqueInstance2->getUniqueInstance3());
        $this->assertNotSame($useUniqueInstance, $useUniqueInstance2);
        
        //Test using a factory and using unique instance in the same container
        $container->define('UseUniqueInstance2', 'UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance'))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance')))
        ->setFactory(array('UseUniqueInstanceFactory', 'create'), array(new ComponentReference('UniqueInstance')));
        
        $useUniqueInstance3 = $container->get('UseUniqueInstance2');
        $this->assertSame($ui, $useUniqueInstance3->getUniqueInstance());
        $this->assertSame($ui, $useUniqueInstance3->getUniqueInstance2());
        $this->assertSame($ui, $useUniqueInstance3->getUniqueInstance3());
        $this->assertNotSame($useUniqueInstance, $useUniqueInstance3);
        
        //test using a factory and using unique instance in another container
        $container2->define('UseUniqueInstance2', 'UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance', $container))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance', $container)))
        ->setFactory(array('UseUniqueInstanceFactory', 'create'), array(new ComponentReference('UniqueInstance', $container)));

        $useUniqueInstance4 = $container2->get('UseUniqueInstance2');
        $this->assertSame($ui, $useUniqueInstance4->getUniqueInstance());
        $this->assertSame($ui, $useUniqueInstance4->getUniqueInstance2());
        $this->assertSame($ui, $useUniqueInstance4->getUniqueInstance3());
        $this->assertNotSame($useUniqueInstance4, $useUniqueInstance3);
    }
    
    public function testWithUnSharedComponent ()
    {
        $container = new Container();
        
        //Setting a shared instance
        $container->define('UniqueInstance')
                  ->setShared(false);

        $ui  = $container->get('UniqueInstance');
        $ui2 = $container->get('UniqueInstance');
        $this->assertNotSame($ui, $ui2);
 
        //testing with a single reference in the same container
        $container->define('UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance'))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance')))
        ->setConstructorArguments(array(new ComponentReference('UniqueInstance')));
        
        $useUniqueInstance = $container->get('UseUniqueInstance');
        $this->assertNotSame($ui, $useUniqueInstance->getUniqueInstance());
        $this->assertNotSame($ui, $useUniqueInstance->getUniqueInstance2());
        $this->assertNotSame($ui, $useUniqueInstance->getUniqueInstance3());
        
        //Testing with an a reference in another container
        $container2 = new Container();
        $container2->define('UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance', $container))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance', $container)))
        ->setConstructorArguments(array(new ComponentReference('UniqueInstance', $container)));

        $useUniqueInstance2 = $container2->get('UseUniqueInstance');
        $this->assertNotSame($ui, $useUniqueInstance2->getUniqueInstance());
        $this->assertNotSame($ui, $useUniqueInstance2->getUniqueInstance2());
        $this->assertNotSame($ui, $useUniqueInstance2->getUniqueInstance3());
        $this->assertNotSame($useUniqueInstance, $useUniqueInstance2);
        
        //Test using a factory and using unique instance in the same container
        $container->define('UseUniqueInstance2', 'UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance'))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance')))
        ->setFactory(array('UseUniqueInstanceFactory', 'create'), array(new ComponentReference('UniqueInstance')));
        
        $useUniqueInstance3 = $container->get('UseUniqueInstance2');
        $this->assertNotSame($ui, $useUniqueInstance3->getUniqueInstance());
        $this->assertNotSame($ui, $useUniqueInstance3->getUniqueInstance2());
        $this->assertNotSame($ui, $useUniqueInstance3->getUniqueInstance3());
        $this->assertNotSame($useUniqueInstance, $useUniqueInstance3);
        
        //test using a factory and using unique instance in another container
        $container2->define('UseUniqueInstance2', 'UseUniqueInstance')
        ->setProperty('_uniqueInstance3', new ComponentReference('UniqueInstance', $container))
        ->setMethod('setUniqueInstance2', array(new ComponentReference('UniqueInstance', $container)))
        ->setFactory(array('UseUniqueInstanceFactory', 'create'), array(new ComponentReference('UniqueInstance', $container)));

        $useUniqueInstance4 = $container2->get('UseUniqueInstance2');
        $this->assertNotSame($ui, $useUniqueInstance4->getUniqueInstance());
        $this->assertNotSame($ui, $useUniqueInstance4->getUniqueInstance2());
        $this->assertNotSame($ui, $useUniqueInstance4->getUniqueInstance3());
        $this->assertNotSame($useUniqueInstance4, $useUniqueInstance3);
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