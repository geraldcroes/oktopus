<?php
use Oktopus\Autoloader;
use Oktopus\ClassParserForPHP5_3;
use Oktopus\Engine;

class AutoloaderTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test register / unregister.
	 */
	public function testRegister (){
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
			$this->fail ('Register twice should launch an exception');
		}catch(Oktopus\AutoloaderException $e){
		}
	}
	
	/**
	 * @expectedException Oktopus\AutoloaderException 
	 */
	public function testNotWritablePath (){
		$autoloader = new Autoloader('/etc/', new ClassParserForPHP5_3());
	}
	
	/**
	 * @expectedException Oktopus\AutoloaderException 
	 */
	public function testNotMkDir (){
		$autoloader = new Autoloader('/etc/OKTOPUS/', new ClassParserForPHP5_3());
	}
	
	public function testEngineAutoloader (){
		//Check that the engine autoloader has the Oktopus temporary files path configured 
		$this->assertEquals (Oktopus\Engine::getTemporaryFilesPath (), Oktopus\Engine::autoloader()->getCachePath ());

		//check that the engine reports changes to the temporary path...
		Engine::setTemporaryFilesPath(null);
		$this->assertEquals (Oktopus\Engine::getTemporaryFilesPath (), Oktopus\Engine::autoloader()->getCachePath ());
	}
	
	public function testAutoloaderRecursiveAndNonRecursive (){
		//testing recursive
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3 ());
		$autoloader->addPath (__DIR__.'/resources/nowarning/', false);

		//we test that we can find foo, foo2 and foo3 (non recursive call)
		$this->assertTrue ($autoloader->autoload('foo'));
		$this->assertTrue ($autoloader->autoload('foo2'));
		$this->assertTrue ($autoloader->autoload('foo3'));
		
		//the class foo\foo is in a subdirectory, won't find it 
		$this->assertFalse ($autoloader->autoload ('foo\\foo'));
		
		//--- Recursive test
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3 ());
		$autoloader->addPath (__DIR__.'/resources/nowarning/');

		//we test that we can find foo, foo2 and foo3 (non recursive call)
		$this->assertTrue ($autoloader->autoload('foo'));
		$this->assertTrue ($autoloader->autoload('foo2'));
		$this->assertTrue ($autoloader->autoload('foo3'));

		//the class foo\foo is in a subdirectory, have to find it 
		$this->assertTrue ($autoloader->autoload ('foo\\foo'));
	}
	
	public function testAutoloaderWarningTwoSameClassesSameFile (){
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/warning/');
		
		$error_handler = $this->getMock ('ErrorHandler', array ('error_handler'));
		$o = new ReflectionObject ($this);
		$error_handler->expects ($this->atLeastOnce())->method ('error_handler');
		set_error_handler (array($error_handler, 'error_handler'));
		$this->assertFalse ($autoloader->autoload ('not_exists'));
	}
	
	public function testAutoloaderWarningTwoSameClassesTwoFile (){
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/warning2files');
		
		$error_handler = $this->getMock ('ErrorHandler', array ('error_handler'));
		$error_handler->expects ($this->atLeastOnce ())->method ('error_handler');
		set_error_handler (array($error_handler, 'error_handler'));
		$this->assertTrue ($autoloader->autoload ('Afoo'));
		restore_error_handler ();
	}
	
	public function testAutoloaderWarningTwoSameNamespaceClassesTwoFile (){
		$autoloader = new AUtoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/warningnamespace2files');
		
		$error_handler = $this->getMock ('ErrorHandler', array ('error_handler'));
		$error_handler->expects ($this->atLeastOnce ())->method ('error_handler');
		set_error_handler (array($error_handler, 'error_handler'));
		$autoloader->autoload ('not_exists');
		restore_error_handler ();
	}
	
	public function testUpdatedFileTimeLoader (){
		//Simple autoload with no cache
		$autoloader = new Autoloader ('/tmp/UpdatedFileTimeLoader/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/');
		$this->assertTrue ($autoloader->autoload ('foo'));
		
		sleep (1);
		touch (__DIR__.'/resources/nowarning/foo.php');
		$autoloader = new Autoloader ('/tmp/UpdatedFileTimeLoader/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/');
		$this->assertTrue ($autoloader->autoload ('foo', true));//asking to check files
		restore_error_handler ();
	}

	public function testAutoloaderCacheAndNoCache (){
		//Simple autoload with no cache
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/');
		$this->assertTrue ($autoloader->autoload ('foo'));

		//Testing several cases, forcing or not the loading of the class
		$autoloader = new Autoloader ('/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/');		
		$this->assertTrue ($autoloader->autoload ('foo'));
		$this->assertTrue ($autoloader->autoload ('foo'));		
		$this->assertFalse ($autoloader->autoload ('FOONOTEXISTS___'));

		//Testing to generate a cache file (tmp/somethingnew)
		$autoloader = new Autoloader ('/tmp/'.uniqid (), new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/');		
		$this->assertTrue ($autoloader->autoload ('foo'));
		
		//Sees if the exception is raised while adding a path to look into that must exists
		$this->setExpectedException ('Oktopus\\AutoloaderException');
		$autoloader->addPath ('AZERTYQWERTY/this/does/not/exists/or/this/is/very/very/bad_luck/'.uniqid (), true);
	}	
}