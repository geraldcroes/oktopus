<?php
class EngineTest extends PHPUnit_Framework_TestCase {
	public function testStartEngine (){
		require_once ('../../Oktopus/Engine.class.php');
		Oktopus\Engine::start ('/tmp/');
		$this->assertEquals (Oktopus\Engine::getMode (), Oktopus\Engine::MODE_DEBUG);
	}

	/**
	 * @depends testStartEngine
	 */
	public function testStartTwite (){
		$this->assertTrue (Oktopus\Autoloader::instance ()->autoload ('Oktopus\\Debug'));
	}

	/**
	 * @depends testStartEngine
	 * @expectedException Oktopus\Exception
	 */
	public function testDoubleException (){
		Oktopus\Engine::start ('/tmp/');
	}

}
