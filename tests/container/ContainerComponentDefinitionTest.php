<?php
class ContainerComponentDefinitionTest extends PHPUnit_Framework_TestCase
{
	public function setUp ()
	{
		require_once __DIR__.'/../bootstrap.php';
	}
	 
	public function testProperties ()
	{
		$cd = new Oktopus\ContainerComponentDefinition('foo');
		$this->assertFalse($cd->hasProperty('foo'));
		$cd->setProperty('foo', 'value');
		$this->assertTrue($cd->hasProperty('foo'));
		$this->assertEquals($cd->getProperty('foo'), 'value');
		
		try {
			$cd->getProperty('foo2');
			$this->fails('Getting an unset property should raise an exception');
		} catch (Oktopus\ContainerComponentDefinitionException $e) {
			$this->assertTrue(true);
		}
		
		//Trying to set a wrong property name
		try {
			$cd->setProperty(array(), 'fooValue');
			$this->fails('Should not be possible to set a property name that is not a string');
		} catch (Oktopus\ContainerComponentDefinitionException $e) {
			$this->assertTrue(true);
		}
	}
	
	public function testMethod ()
	{
		//Testing with a single parameter method name
		$cd = new Oktopus\ContainerComponentDefinition('foo');
		$this->assertFalse($cd->hasMethod('foo'));
		$cd->setMethod('foo', array('value'));
		$this->assertTrue($cd->hasMethod('foo'));
		$this->assertEquals($cd->getMethod('foo'), array('value'));
		try {
			$cd->getMethod('foo2');
			$this->fails('Getting an unset method should raise an exception');
		} catch (Oktopus\ContainerComponentDefinitionException $e) {
			$this->assertTrue(true);
		}
		
		//Testing with no parameters
		$cd->setMethod('foo2');
		$this->assertTrue($cd->hasMethod('foo2'));
		$this->assertEquals($cd->getMethod('foo2'), array());

		//Trying to set a wrong method name
		try {
			$cd->setMethod(array());
			$this->fails('Should not be possible to set a method name that is not a string');
		} catch (Oktopus\ContainerComponentDefinitionException $e) {
			$this->assertTrue(true);
		}
	}

	public function testConstructor ()
	{
		$cd = new Oktopus\ContainerComponentDefinition('foo');
		$this->assertFalse($cd->hasMethod('__construct'));
		$this->assertFalse($cd->hasConstructor());
		$cd->setConstructor(array('value'));
		$this->assertTrue($cd->hasMethod('__construct'));
		$this->assertTrue($cd->hasConstructor());
		$this->assertEquals($cd->getConstructor(), array('value'));
		$this->assertEquals($cd->getMethod('__construct'), array('value'));
	}
	
	public function testShared ()
	{
		$cd = new Oktopus\ContainerComponentDefinition('foo');
		$this->assertTrue($cd->isShared());//default shared value is true
		
		$cd->setShared(false);
		$this->assertFalse($cd->isShared());
	}
	
	public function testClass ()
	{
		$cd = new Oktopus\ContainerComponentDefinition('foo');
		try {
			$cd->getClass();
			$this->fails('Getting the class if not set should raise an exception');
		} catch (Oktopus\ContainerComponentDefinitionException $e) {
			$this->assertTrue(true);
		}

		$cd->setClass('UneClass');
		$this->assertEquals('UneClass', $cd->getClass());
		
		//trying to set a incorrect classname
		try {
			$cd->setClass(array());
			$this->fails('Setting a classname that is not a string should raise an exception');
		} catch (Oktopus\ContainerComponentDefinitionException $e) {
			$this->assertTrue(true);
		}
	}
}