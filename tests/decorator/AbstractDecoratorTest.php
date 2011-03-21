<?php
class AbstractDecoratorTest extends PHPUnit_Framework_TestCase
{
   public function setUp ()
   {
      require_once __DIR__.'/../bootstrap.php';
   }

   public function testConstructDecorator ()
   {
      $stdClass = new StdClass();
      $decorator = new Oktopus\AbstractDecorator($stdClass);

      try {
         $decorator = new Oktopus\AbstractDecorator(array());
         $this->fails('A decorator should not accept not objects in its constructor');
      } catch (Oktopus\DecoratorException $e) {
         $this->assertTrue(true);
      }
   }

   public function testCallMethod ()
   {
      //Assert a simple method call
   	  $mock = $this->getMock('Decorated', array('testMethod'));
      $mock->expects($this->atLeastOnce())->method('testMethod');
      
      $decorator = new Oktopus\AbstractDecorator($mock);
      $decorator->testMethod();

      //Assert a method call with one parameter
      $mock = $this->getMock('Decorated', array('testMethod'));
      $mock->expects($this->atLeastOnce())
           ->method('testMethod')
           ->with($this->equalTo('something'));
           
      $decorator = new Oktopus\AbstractDecorator($mock);
      $decorator->testMethod('something');
      
      //Assert a method call with two parameters
      $mock = $this->getMock('Decorated', array('testMethod'));
      $mock->expects($this->atLeastOnce())
           ->method('testMethod')
           ->with($this->equalTo('something'), $this->equalTo('something2'));

      $decorator = new Oktopus\AbstractDecorator($mock);
      $decorator->testMethod('something', 'something2');
      
      //Assert that the decorator returns the decorated value in method call
      $mock = $this->getMock('Decorated', array('testMethod'));
      $mock->expects($this->atLeastOnce())
           ->method('testMethod')
           ->with($this->equalTo('something'), $this->equalTo('something2'))
           ->will($this->returnValue('foo'));

      $decorator = new Oktopus\AbstractDecorator($mock);
      $this->assertEquals ('foo', $decorator->testMethod('something', 'something2'));
   }

   public function testProperties ()
   {
      $stdClass = new StdClass();
      $decorator = new Oktopus\AbstractDecorator($stdClass);
      
      //assert that the decorator sets the value to the decorated object
      $decorator->property = 'new value';
      $this->assertEquals('new value', $decorator->property);
      $this->assertEquals($stdClass->property, $decorator->property);

      //Assert that unset properties are not set, and set properties are set :-)
      $this->assertFalse(isset($decorator->unsetProperty));
      $this->assertTrue(isset($decorator->property));
   }

   public function testInvoke ()
   {
      $mock = $this->getMock('Decorated', array('__invoke'));
      $mock->expects($this->once())->method('__invoke');
      
      $decorator = new Oktopus\AbstractDecorator($mock);
      $decorator();
   }
}