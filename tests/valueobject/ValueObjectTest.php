<?php
class ValueObjectTest extends PHPUnit_Framework_TestCase {
    public function testCreate (){
        //Creating a value object from an array
        $test = array('p1'=>1, 'p2'=>2, 'p3'=>3);
        $valueObject = new Oktopus\ValueObject($test);
        
        $this->assertEquals($valueObject->p1, $test['p1']);
        $this->assertEquals($valueObject->p2, $test['p2']);
        $this->assertEquals($valueObject->p3, $test['p3']);
        
        $valueObject = new Oktopus\ValueObject('p1=>1;p2=>2;p3=>3');
        $this->assertEquals($valueObject->p1, $test['p1']);
        $this->assertEquals($valueObject->p2, $test['p2']);
        $this->assertEquals($valueObject->p3, $test['p3']);
    }
}