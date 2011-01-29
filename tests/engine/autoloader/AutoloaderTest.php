<?php
use Oktopus\Autoloader;
use Oktopus\ClassParserForPHP5_3;

class AutoloaderTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test register / unregister.
	 */
	public function testAutoloaderCreate (){
		$autoloader = new Autoloader('/tmp/', new ClassParserForPHP5_3());
		$this->assertFalse ($autoloader->isRegistered());
		$autoloader->register();
		$this->assertTrue($autoloader->isRegistered());
		
		$autoloader2 = new Autoloader('/tmp/', new ClassParserForPHP5_3());
		$this->assertFalse ($autoloader2->isRegistered());
		$autoloader2->register();
		$this->assertTrue($autoloader2->isRegistered());
		$autoloader2->unregister ();
		$this->assertFalse ($autoloader2->isRegistered());
		$this->assertTrue($autoloader->isRegistered());
	}
}