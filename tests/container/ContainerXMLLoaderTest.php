<?php
class ContainerXMLLoaderTest extends PHPUnit_Framework_TestCase
{
	public function setUp ()
	{
		require_once __DIR__.'/../bootstrap.php';
		Oktopus\Engine::autoloader()->addPath(__DIR__.'/../resources/container/');
	}

    public function testBasicLoading ()
    {
        $nakedContainer = new Oktopus\Container();
        $container = new Oktopus\ContainerXMLLoader($nakedContainer);
        
        $container->addXmlFile(__DIR__.'/../resources/container/basics.xml');
        $this->assertFalse($container->hasComponent('notInContainer'));
        $this->assertTrue($container->hasComponent('Fruit'));
        $this->assertTrue($container->hasComponent('Apple'));
        $this->assertTrue($container->hasComponent('Juicer'));
        
        //creating objects
        $apple  = $container->get('Apple');
        $juicer = $container->get('Juicer');
        $tool = $container->get('Tool');
        $this->assertSame($apple, $juicer->getFruit());
        $this->assertSame($juicer->getTool(), $tool);
        
        //craeting unshared components
        $fruit  = $container->get('Fruit');
        $fruit2 = $container->get('Fruit');
        $this->assertNotSame($fruit, $fruit2);
        
        //creating object with reference configured with attributes and tags
        $juicer1 = $container->get('Juicer1');
        $this->assertNotSame($juicer1, $juicer);
        $this->assertSame($juicer1->getFruit(), $apple);
        $this->assertSame($juicer1->getTool(), $tool);
        
        //creating an object with a simple value as its property
        $easyPrivate = $container->get('EasyPrivate');
        $this->assertEquals('propertyValue', $easyPrivate->getProperty());
        
        //creating an object with a simple value as its property declared in a tag
        $easyPrivate1 = $container->get('EasyPrivate1');
        $this->assertEquals('propertyValue', $easyPrivate1->getProperty());
        
        //creating an object with a simple empty value as its property declared in an attribute
        $easyPrivate2 = $container->get('EasyPrivate2');
        $this->assertEquals('', $easyPrivate2->getProperty());
        
        //creating an object with a simple empty value as its property declared in a tag
        $easyPrivate3 = $container->get('EasyPrivate3');
        $this->assertEquals('', $easyPrivate3->getProperty());
        
        //creating an object with a simple empty value as its property declared in a tag
        $easyPrivate4 = $container->get('EasyPrivate4');
        $this->assertNull($easyPrivate4->getProperty());        
    }
    
    public function testUnreadableFile ()
    {
        $nakedContainer = new Oktopus\Container();
        $container = new Oktopus\ContainerXMLLoader($nakedContainer);
        
        try {
           $container->addXmlFile(__DIR__.'/../resources/container/fileDoesNotExists');
           $this->fails('Trying to add a non existant XML file should raise an error');
        } catch (Oktopus\ContainerException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testUnsetComponentId ()
    {
        $nakedContainer = new Oktopus\Container();
        $container = new Oktopus\ContainerXMLLoader($nakedContainer);
        
        try {
           $container->addXmlFile(__DIR__.'/../resources/container/fail_unset_component_id.xml');
           $this->fails('Trying to load an XML file with a component that do not have an id should raise an exception');
        } catch (Oktopus\ComponentDefinitionException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testUnsetPropertyName ()
    {
        $nakedContainer = new Oktopus\Container();
        $container = new Oktopus\ContainerXMLLoader($nakedContainer);
        
        try {
           $container->addXmlFile(__DIR__.'/../resources/container/fail_unset_property_name.xml');
           $this->fails('Trying to load an XML file with a component that have a component with a missing property name should raise an exception');
        } catch (Oktopus\ComponentDefinitionException $e) {
            $this->assertTrue(true);
        }
    }

    public function testUnsetPropertyValue ()
    {
        $nakedContainer = new Oktopus\Container();
        $container = new Oktopus\ContainerXMLLoader($nakedContainer);
        
        try {
           $container->addXmlFile(__DIR__.'/../resources/container/fail_unset_property_value.xml');
           $this->fails('Trying to load an XML file with a component that have a component with a missing property value should raise an exception');
        } catch (Oktopus\ComponentDefinitionException $e) {
            $this->assertTrue(true);
        }
    }
}