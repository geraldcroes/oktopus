<?php
class ValueObjectTest extends PHPUnit_Framework_TestCase {
   public function setUp () {
      require_once __DIR__.'/../bootstrap.php';
   }


    public function testCreate (){
        //Creating a value object from an array
        $test = array('p1'=>1, 'p2'=>2, 'p3'=>3);
        $valueObject = new Oktopus\ValueObject($test);
        
        $this->assertEquals($valueObject->p1, $test['p1']);
        $this->assertEquals($valueObject->p2, $test['p2']);
        $this->assertEquals($valueObject->p3, $test['p3']);
        
        $valueObject = new Oktopus\ValueObject('p1=>1;p2=>2;p3=>3;test;test2');
        $this->assertEquals($valueObject->p1, '1');
        $this->assertEquals($valueObject->p2, '2');
        $this->assertEquals($valueObject->p3, '3');
	$this->assertEquals($valueObject[0], 'test');
	$this->assertEquals($valueObject[1], 'test2');

	$stdC = new StdClass();
	$stdC->test  = 1;
	$stdC->test2 = 2;
	$stdC->test3 = 3;
	$valueObject = new Oktopus\ValueObject($stdC);
	$this->assertEquals($valueObject->test, 1);
	$this->assertEquals($valueObject->test2, 2);
	$this->assertEquals($valueObject->test3, 3);
    }

   public function testIsset () {
      $test = 'p1=>1;p2=>2;p3=>3';
      $valueObject = new Oktopus\ValueObject($test);
      
      $this->assertFalse(isset($valueObject->notSet));
      $this->assertTrue (isset($valueObject->p1));

      $this->assertFalse(isset($valueObject['notSet']));
      $this->assertTrue(isset($valueObject['p1']));
   }

   public function testUnset () {
      $valueObject = new Oktopus\ValueObject('p1=>1;p2=>2;p3=>3');
      $this->assertTrue(isset($valueObject['p1']));
      unset($valueObject['p1']);

      $this->assertFalse(isset($valueObject['p1']));
      $this->assertFalse(isset($valueObject->p1));
   }
}
