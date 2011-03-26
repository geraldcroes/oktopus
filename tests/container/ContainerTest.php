<?php
class ContainerTest extends PHPUnit_Framework_TestCase
{
	public function setUp ()
	{
		require_once __DIR__.'/../bootstrap.php';
	}
	
	public function testBasicConstruction ()
	{
		$container = new Oktopus\Container();
		$container->define('foo')
		          ->setClass('foo')
		          ->setProperty('_fooDirect', '_fooDirect')
		          ->setMethod('setFoo', array('foo'))
		          ->setMethod('setFoo2', array('foo2'))		          
		          ->setConstructor(array('foo1', 'foo2'));
		$foo = $container->get('foo');
		
		$this->assertEquals($foo->getFooDirect(), '_fooDirect');
		$this->assertEquals($foo->getFoo(), 'foo');
		$this->assertEquals($foo->getFoo2(), 'foo2');
		$this->assertEquals($foo->getFirstParameter(), 'foo1');
		$this->assertEquals($foo->getSecondParameter(), 'foo2');
	}
	
	public function testLazyConstruction ()
	{
		$mockFoo = $this->getMock('MockFoo', array('fooDirect', 'setFoo', 'setFoo2', 'firstParameter', 'secondParameter'));
		$mockFoo->expects($this->once())
		        ->method('fooDirect');
		$mockFoo->expects($this->once())
		        ->method('setFoo');
		$mockFoo->expects($this->once())
		        ->method('setFoo2');
		$mockFoo->expects($this->once())
		        ->method('firstParameter');
		$mockFoo->expects($this->once())
		        ->method('secondParameter');
	        
		$container = new Oktopus\Container();
		$container->define('foo')
		          ->setClass('foo')
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
		          ->setConstructor(array(
		             function() use($mockFoo){
		          	    $mockFoo->firstParameter();
		          	    return 'foo1';
		             }, 
		             function() use($mockFoo){
		          	    $mockFoo->secondParameter();
		          	    return 'foo2';
		             }));

		$foo = $container->get('foo');
		
		$this->assertEquals($foo->getFooDirect(), '_fooDirect');
		$this->assertEquals($foo->getFoo(), 'foo');
		$this->assertEquals($foo->getFoo2(), 'foo2');
		$this->assertEquals($foo->getFirstParameter(), 'foo1');
		$this->assertEquals($foo->getSecondParameter(), 'foo2');
	}	
}

class Foo
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
			throw new Exception('setFoo should have been called before setFoo2');
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

class CallOrderTester
{
}