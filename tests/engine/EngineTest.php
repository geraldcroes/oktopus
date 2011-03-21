<?php
class EngineTest extends PHPUnit_Framework_TestCase {
	public function setUp (){
		require (__DIR__.'/../../Oktopus/Engine.php');
	}
	public function testCreate (){
		try {
			Oktopus\Engine::autoloader();
			$this->fail('Should have raised an exception');
		}catch (Exception $e){
		}

		try{
			Oktopus\Engine::start ('/tmp/', 56756785678567856785678);
			$this->fail('Should have raised an exception');
		}catch(InvalidArgumentException $e){
		}
	}
}
