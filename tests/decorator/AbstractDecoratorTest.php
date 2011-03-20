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
      $mock = $this->getMock('Decorated', array('testMethod'));
      $mock->expects($this->atLeastOnce())->method('testMethod');
      
      $decorator = new Oktopus\AbstractDecorator($mock);
      $mock->testMethod();
   }

   public function testProperties ()
   {
      $stdClass = new StdClass();
      $decorator = new Oktopus\AbstractDecorator($stdClass);
      
      $decorator->property = 'new value';
      $this->assertEquals('new value', $decorator->property);
      
      $this->assertFalse(isset($decorator->unsetProperty));
   }

   public function testInvoke ()
   {
      $mock = $this->getMock('Decorated', array('__invoke'));
      $mock->expects($this->once())->method('__invoke');
      
      $decorator = new Oktopus\AbstractDecorator($mock);
      $decorator();
   }
}
