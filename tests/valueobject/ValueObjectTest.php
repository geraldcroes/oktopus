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
		
		$valueObject = new Oktopus\ValueObject($valueObject);
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
	
	public function testArray() {
		$valueObject = new Oktopus\ValueObject('p1=>1;p2=>2;p3=>3;4');
		$valueObject[] = '5';
		$this->assertEquals($valueObject[1], '5');
		$valueObject[1] = '6';
		$this->assertEquals($valueObject[1], '6');
		$valueObject[1] = '7';
		$this->assertEquals($valueObject[1], '7');

		$valueObject = new Oktopus\ValueObject();
		$valueObject[] = '5';
		$this->assertEquals($valueObject[0], '5');
		$valueObject[1] = '6';
		$this->assertEquals($valueObject[1], '6');
		$valueObject[1] = '7';
		$this->assertEquals($valueObject[1], '7');
		
		$valueObject[2][] = '7';
		$this->assertEquals($valueObject[2][0], '7');
	}
	
	public function testMerge () {
		$test = array('p1'=>1, 'p2'=>2, 'p3'=>3);
		$valueObject = new Oktopus\ValueObject($test);
		$valueObject2 = new Oktopus\ValueObject(array('p4'=>4));
		$valueObject->mergeWith($valueObject2);
		$this->assertEquals($valueObject->p1, $test['p1']);
		$this->assertEquals($valueObject->p2, $test['p2']);
		$this->assertEquals($valueObject->p3, $test['p3']);
		$this->assertEquals($valueObject->p4, 4);
		$this->assertFalse(isset($valueObject2->p1));
		$this->assertFalse(isset($valueObject2->p2));		
		$this->assertFalse(isset($valueObject2->p3));		
		
		$valueObject = new Oktopus\ValueObject($test);
		$valueObject2 = new Oktopus\ValueObject(array('p4'=>4));
		$valueObject2->mergeWith($valueObject);
		$this->assertEquals($valueObject2->p1, $test['p1']);
		$this->assertEquals($valueObject2->p2, $test['p2']);
		$this->assertEquals($valueObject2->p3, $test['p3']);
		$this->assertEquals($valueObject2->p4, 4);
		$this->assertFalse(isset($valueObject->p4));		
		
		$valueObject2 = new Oktopus\ValueObject(array('p4'=>4));
		$valueObject2->mergeWith($test);
		$this->assertEquals($valueObject2->p1, $test['p1']);
		$this->assertEquals($valueObject2->p2, $test['p2']);
		$this->assertEquals($valueObject2->p3, $test['p3']);
		$this->assertEquals($valueObject2->p4, 4);

		$test = new StdClass();
		$test->p1 = 1;
		$test->p2 = 2;
		$test->p3 = 3;
		$valueObject2 = new Oktopus\ValueObject(array('p4'=>4));
		$valueObject2->mergeWith($test);
		$this->assertEquals($valueObject2->p1, $test->p1);
		$this->assertEquals($valueObject2->p2, $test->p2);
		$this->assertEquals($valueObject2->p3, $test->p3);
		$this->assertEquals($valueObject2->p4, 4);
	}
}