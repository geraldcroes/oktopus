<?php
class EngineTest extends PHPUnit_Framework_TestCase {
	public function testStartEngine (){
		require_once ('./Oktopus/Engine.class.php');
		Oktopus\Engine::start ('/tmp/');
		$this->assertEquals (Oktopus\Engine::getMode (), Oktopus\Engine::MODE_DEBUG);
	}

	/**
	 * @depends testStartEngine
	 * @expectedException Oktopus\Exception
	 */
	public function testDoubleLaunchException (){
		Oktopus\Engine::start ('/tmp/');
	}

}
