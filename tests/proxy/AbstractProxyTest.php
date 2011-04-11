<?php
class AbstractProxyTest extends PHPUnit_Framework_TestCase
{
   public function setUp ()
   {
      require_once __DIR__.'/../bootstrap.php';
   }

   public function testConstructProxy ()
   {
      $stdClass = new StdClass();
      $Proxy = new Oktopus\AbstractProxy($stdClass);

      try {
         $Proxy = new Oktopus\AbstractProxy(array());
         $this->fails('A Proxy should not accept not objects in its constructor');
      } catch (Oktopus\ProxyException $e) {
         $this->assertTrue(true);
      }
   }

   public function testCallMethod ()
   {
      //Assert a simple method call
   	  $mock = $this->getMock('Proxied', array('testMethod'));
      $mock->expects($this->atLeastOnce())
           ->method('testMethod')
           ->will($this->returnValue('foo'));
      
      $Proxy = new Oktopus\AbstractProxy($mock);
      $this->assertEquals ('foo', $Proxy->testMethod());

      //Assert a method call with one parameter
      $mock = $this->getMock('Proxied', array('testMethod'));
      $mock->expects($this->atLeastOnce())
           ->method('testMethod')
           ->with($this->equalTo('something'))
           ->will($this->returnValue('foo'));
           
      $Proxy = new Oktopus\AbstractProxy($mock);
      $this->assertEquals ('foo', $Proxy->testMethod('something'));
      
      //Assert a method call with two parameters
      $mock = $this->getMock('Proxied', array('testMethod'));
      $mock->expects($this->atLeastOnce())
           ->method('testMethod')
           ->with($this->equalTo('something'), $this->equalTo('something2'))
           ->will($this->returnValue('foo'));

      $Proxy = new Oktopus\AbstractProxy($mock);
      $this->assertEquals ('foo', $Proxy->testMethod('something', 'something2'));
   }

   public function testProperties ()
   {
      $stdClass = new StdClass();
      $Proxy = new Oktopus\AbstractProxy($stdClass);
      
      //assert that the Proxy sets the value to the Proxied object
      $Proxy->property = 'new value';
      $this->assertEquals('new value', $Proxy->property);
      $this->assertEquals($stdClass->property, $Proxy->property);

      //Assert that unset properties are not set, and set properties are set :-)
      $this->assertFalse(isset($Proxy->unsetProperty));
      $this->assertTrue(isset($Proxy->property));
   }

   public function testInvoke ()
   {
      //simple invoke
   	  $mock = $this->getMock('Proxied', array('__invoke'));
      $mock->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue('foo'));
      
      $Proxy = new Oktopus\AbstractProxy($mock);
      $this->assertEquals ('foo', $Proxy());
      
      //invoke with one paramter
   	  $mock = $this->getMock('Proxied', array('__invoke'));
      $mock->expects($this->once())
            ->method('__invoke')
            ->with($this->equalTo('something'))
            ->will($this->returnValue('foo'));
      
      $Proxy = new Oktopus\AbstractProxy($mock);
      $this->assertEquals ('foo', $Proxy('something'));
      
      //invoke with two parameters
   	  $mock = $this->getMock('Proxied', array('__invoke'));
      $mock->expects($this->once())
            ->method('__invoke')
            ->with($this->equalTo('something'), $this->equalTo('something2'))
            ->will($this->returnValue('foo'));
      
      $Proxy = new Oktopus\AbstractProxy($mock);
      $this->assertEquals('foo', $Proxy('something', 'something2'));
   }
}