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

	public function testKnownClasses (){
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/', false);

		//No class has been loaded.
		$this->assertEquals (array(), $autoloader->getKnownClasses());
		$this->assertTrue ($autoloader->autoload('foo'));

		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);

		$this->assertContains('foo', $values);
		$this->assertContains('foo2', $values);
		$this->assertContains('foo3', $values);
		$this->assertEquals(3, count($values));
	}
	
	public function testIncludesAll (){
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/', false);

		//No class has been loaded.
		$this->assertEquals(array(), $autoloader->getKnownClasses());
		$autoloader->includesAll();

		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
		$this->assertContains('foo', $values);
		$this->assertContains('foo2', $values);
		$this->assertContains('foo3', $values);
		$this->assertEquals(3, count($values));
		
		$autoloader->addPath(__DIR__.'/resources/nowarning/namespaces', false);

		//Still not known, adding a path does not trigger the autoload
		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
		$this->assertContains('foo', $values);
		$this->assertContains('foo2', $values);
		$this->assertContains('foo3', $values);
		$this->assertEquals(3, count($values));
		
		$autoloader->autoload ('foo');
		//Still not known, adding the cache should be enought
		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
		$this->assertContains('foo', $values);
		$this->assertContains('foo2', $values);
		$this->assertContains('foo3', $values);
		$this->assertEquals(3, count($values));
		
		$autoloader->includesAll ();
		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
		$this->assertContains('foo', $values);
		$this->assertContains('foo2', $values);
		$this->assertContains('foo3', $values);
		$this->assertEquals(3, count($values));
		
		list(, $values) = each($knownClasses);
		$this->assertContains('foo\\foo', $values);
		$this->assertContains('foofoo', $values);
		$this->assertContains('foo2\\foo', $values);
		$this->assertContains('foo2\\foo2', $values);
		$this->assertContains('foo3\\foo', $values);
		$this->assertEquals(5, count($values));
	}
	
	/**
	 * @expectedException Oktopus\AutoloaderException
	 */
	public function testCannotWriteInCache (){
		if (is_dir($dir = '/tmp/nowriteincache/')) {
			chmod('/tmp/nowriteincache/', 0700); 
     		$objects = scandir($dir); 
     		foreach ($objects as $object) { 
       			if ($object != "." && $object != "..") { 
         			if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       			}	 
     		}
		} 

		$autoloader = new Autoloader ('/tmp/nowriteincache/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/', false);
		chmod('/tmp/nowriteincache/', 0400);
		$autoloader->includesAll ();
	}
	
	public function testCannotWriteInCacheFile (){
		$autoloader = new Autoloader ('/tmp/nowriteincache2/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/', false);
		$autoloader->includesAll ();
		
		//cache should have been writen, going to make it read only
		$directory = new RecursiveIteratorIterator (new RecursiveDirectoryIterator('/tmp/nowriteincache2/'));
		foreach ($directory as $element){
			chmod($element->getPathName(), 0400);
		}

		//now trying to raise the exception
		$autoloader = new Autoloader ('/tmp/nowriteincache2/', new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/resources/nowarning/', false);
		try{
			$autoloader->includesAll ();
			$this->fail('Includes all should raise an exception (should not be able to write its cache)');
		}catch(Oktopus\AutoloaderException $e){
			$this->assertTrue (true);//ok, raised an exception
		}
		
		//Back to writable
		$directory = new RecursiveIteratorIterator (new RecursiveDirectoryIterator('/tmp/nowriteincache2/'));
		foreach ($directory as $element){
			chmod($element->getPathName(), 0700);
		}
	}	
}