<?php
class EngineProductionTest extends PHPUnit_Framework_TestCase {
	public function setUp (){
		require (__DIR__.'/../../Oktopus/Engine.php');
	}
	public function testCreate (){
		Oktopus\Engine::start ('/tmp/', Oktopus\Engine::MODE_PRODUCTION);
		$this->assertEquals (ini_get('display_errors'), 0);
	}
}
