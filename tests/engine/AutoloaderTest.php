<?php
use Oktopus\Autoloader;
use Oktopus\ClassParserForPHP5_3;
use Oktopus\Engine;

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
		
		try {
			$autoloader->register ();
			$this->failed ('Register twice should launch an exception');
		}catch(Oktopus\AutoloaderException $e){
		}
	}
	
	/**
	 * @expectedException Oktopus\AutoloaderException 
	 */
	public function testAutoloadNotWritablePath (){
		$autoloader = new Autoloader('/etc/', new ClassParserForPHP5_3());
	}
	
	/**
	 * @expectedException Oktopus\AutoloaderException 
	 */
	public function testAutoloadNotMkDir (){
		$autoloader = new Autoloader('/etc/OKTOPUS/', new ClassParserForPHP5_3());
	}
	
	public function testEngineAutoloader (){
		//Check that the engine autoloader has the Oktopus temporary files path configured 
		$this->assertEquals (Oktopus\Engine::getTemporaryFilesPath (), Oktopus\Engine::autoloader()->getCachePath ());

		//check that the engine reports changes to the temporary path...
		Engine::setTemporaryFilesPath(null);
		$this->assertEquals (Oktopus\Engine::getTemporaryFilesPath (), Oktopus\Engine::autoloader()->getCachePath ());
	}
	
	public function testAutoloaderWarningTwoSameClasses (){
		$autoloader = new AUtoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/');
		
		$this->setExpectedException ('PHPUnit_Framework_ERROR');
		$autoloader->autoload ('foo');
	}
	
	public function testAutoloaderCacheAndNoCache (){
		//Simple autoload with no cache
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/');
		$this->assertTrue (@$autoloader->autoload ('foo'));

		//Testing several cases, forcing or not the loading of the class
		$autoloader = new Autoloader ('/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/');		
		$this->assertTrue (@$autoloader->autoload ('foo', false));
		$this->assertTrue (@$autoloader->autoload ('foo', true));		
		$this->assertFalse (@$autoloader->autoload ('FOONOTEXISTS___'));

		//Testing to generate a cache file (tmp/somethingnew)
		$autoloader = new Autoloader ('/tmp/'.uniqid (), new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/');		
		$this->assertTrue (@$autoloader->autoload ('foo'));
		
		//Sees if the exception is raised while adding a path to look into that must exists
		$this->setExpectedException ('Oktopus\\AutoloaderException');
		$autoloader->addPath ('AZERTYQWERTY/this/does/not/exists/or/this/is/very/very/bad_luck/'.uniqid (), true);
	}	
}