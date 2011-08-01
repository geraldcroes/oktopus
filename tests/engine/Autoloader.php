<?php
namespace Oktopus\tests\units;

require __DIR__.'/../bootstrap.php';

use \mageekguy\atoum;


class Autoloader extends atoum\test {
	/**
     * Tries to autoload a previously known class (in the cache) that has been deleted.
     * Should not raise any error
     */
    public function testAutoloadKnownClassThatHasBeenDeleted (){
		//Remove old cache file if it exists and copy sources to test
		exec('rm -Rf /tmp/OktopusTest');
		mkdir('/tmp/OktopusTest/testDeleteFile/sources', 0775, true);
		exec('cp -R '.__DIR__.'/../resources/nowarning/* /tmp/OktopusTest/testDeleteFile/sources/');
		//will generate cache file for every classes (including foo)
        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assert
                ->boolean($autoloader->autoload ('foo2'))
                ->isTrue();
		
		//will try to load foo after deleting the autoloader cache
	        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$autoloader->autoload('foo2');//to include the cache and tells where foo should also be founded.

	        exec('rm /tmp/OktopusTest/testDeleteFile/sources/foo.php');//deleting the class foo

		//Now trying to load foo that does not exists anymore
        $this->assert
                ->boolean($autoloader->autoload('foo'))
                ->isFalse();
	}

    /**
     * Tries to autoload a previously known class (in the cache) that has been moved around in production mode
     */
	public function testAutoloadKnownClassThatHasBeenMovedProductionMode (){
		//Remove old cache file if it exists and copy sources to test
		exec('rm -Rf /tmp/OktopusTest/');
		mkdir('/tmp/OktopusTest/testDeleteFile/sources/', 0775, true);
		exec('cp -R '.__DIR__.'/../resources/nowarning/* /tmp/OktopusTest/testDeleteFile/sources/');

		//will generate cache file for every classes (including foo)
	        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assert
                ->boolean($autoloader->autoload ('foo2'))
                ->isTrue();

		$autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');

		exec('mv /tmp/OktopusTest/testDeleteFile/sources/foo.php /tmp/OktopusTest/testDeleteFile/sources/foomoved.php');

		//This test should be run considering Oktopus is in PRODUCTION_MODE
        $this->assert
                ->boolean(\Oktopus\Engine::getMode() === \Oktopus\Engine::MODE_PRODUCTION)
                ->isTrue();

		//Tries to autoload the moved class without allowing the autoloader to refresh its cache
        $this->assert
                ->boolean($autoloader->autoload('foo', false))
                ->isFalse();

        //Tries to autoload the moved class allowing the autoloader to refresh its cache
        $this->assert
                ->boolean($autoloader->autoload('foo', true))
                ->isTrue();
	}

    /**
     * Tries to autoload a previously known class (in the cache) that has been moved around not in production mode
     */
	public function testAutoloadKnownClassThatHasBeenMovedNotProductionMode (){
        //Restarting the engine in debug mode
        \Oktopus\Engine::autoloader()->unregister();
        \Oktopus\Engine::start ('/tmp/', \Oktopus\Engine::MODE_DEBUG);
        \Oktopus\Debug::unregisterErrorHandler();
        \Oktopus\Debug::unregisterExceptionHandler();

		//Remove old cache file if it exists and copy sources to test
		exec('rm -Rf /tmp/OktopusTest');
		mkdir('/tmp/OktopusTest/testDeleteFile/sources/', 0775, true);
		exec('cp -R '.__DIR__.'/../resources/nowarning/* /tmp/OktopusTest/testDeleteFile/sources/');

		//will generate cache file for every classes (including foo)
        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assert
                ->boolean($autoloader->autoload ('foo2'))
                ->isTrue();

		$autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');

		exec('mv /tmp/OktopusTest/testDeleteFile/sources/foo.php /tmp/OktopusTest/testDeleteFile/sources/foomoved.php');

		//This test should be run considering Oktopus is in PRODUCTION_MODE
        $this->assert
                ->boolean(\Oktopus\Engine::getMode() !== \Oktopus\Engine::MODE_PRODUCTION)
                ->isTrue();

		//Tries to autoload the moved class without allowing the autoloader to refresh its cache
        $this->assert
                ->boolean($autoloader->autoload('foo', false))
                ->isTrue();

        //Tries to autoload the moved class allowing the autoloader to refresh its cache
        $this->assert
                ->boolean($autoloader->autoload('foo', true))
                ->isTrue();
	}

    /**
     * Test register / unregister.
     */
    public function testRegister (){
        $autoloader = new \Oktopus\Autoloader('/tmp/', new \Oktopus\ClassParserForPHP5_3());
        $this->assert
                ->boolean($autoloader->isRegistered())
                ->isFalse();

        $autoloader->register();
        $this->assert
                ->boolean($autoloader->isRegistered())
                ->isTrue();

        $autoloader2 = new \Oktopus\Autoloader('/tmp/', new \Oktopus\ClassParserForPHP5_3());
        $this->assert
                ->boolean($autoloader2->isRegistered())
                ->isFalse();

        $autoloader2->register();
        $this->assert
                ->boolean($autoloader2->isRegistered())
                ->isTrue();

        $autoloader2->unregister ();
        $this->assert
                ->boolean($autoloader2->isRegistered())
                ->isFalse();
        $this->assert
                ->boolean($autoloader->isRegistered())
                ->isTrue();

        //Cannot register the same autoloader twice
        $this->assert
                ->exception(function () use($autoloader){$autoloader->register ();})
                ->IsInstanceOf('\Oktopus\AutoloaderException');
    }

    /**
     * Testing if giving the autoloader a path to a cache without writable permissions launch an exception
     */
    public function testNotWritablePath (){
        //The path is not writable
        $this->assert
                ->exception(function(){new \Oktopus\Autoloader('/etc/', new \Oktopus\ClassParserForPHP5_3());})
                ->isInstanceOf('\Oktopus\AutoloaderException');

		//The path is not writable (we will asks Oktopus to try to create a subdirectory)
        $this->assert
                ->exception(function(){new \Oktopus\Autoloader('/etc/OKTOPUS/', new \Oktopus\ClassParserForPHP5_3());})
                ->isInstanceOf('\Oktopus\AutoloaderException');
    }

    public function testAutoloaderRecursiveAndNonRecursive (){
        //testing recursive
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3 ());
        $autoloader->addPath (__DIR__.'/../resources/nowarning/', false);

        //we test that we can find foo, foo2 and foo3 (non recursive call)
        $this->assert->boolean($autoloader->autoload('foo'))->isTrue();
        $this->assert->boolean($autoloader->autoload('foo2'))->isTrue();
        $this->assert->boolean($autoloader->autoload('foo3'))->isTrue();

        //the class foo\foo is in a subdirectory, won't find it
        $this->assert->boolean($autoloader->autoload ('foo\\foo'))->isFalse();

        //--- Recursive test
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3 ());
        $autoloader->addPath (__DIR__.'/../resources/nowarning/');

        //we test that we can find foo, foo2 and foo3 (non recursive call)
        $this->assert->boolean ($autoloader->autoload('foo'))->isTrue();
        $this->assert->boolean ($autoloader->autoload('foo2'))->isTrue();
        $this->assert->boolean ($autoloader->autoload('foo3'))->isTrue();

        //the class foo\foo is in a subdirectory, have to find it
        $this->assert->boolean ($autoloader->autoload ('foo\\foo'))->isTrue();
    }

    public function testAutoloaderWarningTwoSameClassesSameFile (){
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $autoloader->addPath(__DIR__.'/../resources/warning/');
        $this->assert
                ->boolean($autoloader->autoload ('not_exists'))
                ->isFalse();
        $this->assert
		->error()
		->exists()//first error
		->exists();//second error

        //Silent mode
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $autoloader->addPath(__DIR__.'/../resources/warning/')
                    ->setSilentDuplicatesInSameFile(true);
        $this->assert
                ->boolean($autoloader->autoload ('not_exists'))
                ->isFalse();
    }

	public function testAutoloaderWarningTwoSameClassesTwoFile (){
		$autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warning2files');

		$this->assert
                ->boolean($autoloader->autoload ('Afoo'))
                ->isTrue();
        $this->assert
                ->error
                ->exists()
                ->exists()
                ->exists();

        //Silent mode (different files)
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warning2files')
                    ->setSilentDuplicatesInDifferentFiles(true);

        $this->assert
                ->boolean($autoloader->autoload ('Afoo'))
                ->isTrue();

        $this->assert
                ->error
                ->exists();//Just one error a AFoo is declared twice in the same file
                //other errors won't show up

        //Silent mode (same files)
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warning2files')
                    ->setSilentDuplicatesInSameFile(true);

        $this->assert
                ->boolean($autoloader->autoload ('Afoo'))
                ->isTrue();

        $this->assert
                ->error
                ->exists()
                ->exists();//Two errors as the AFoo declared in the same file won't show up

        //Silent mode
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warning2files')
                    ->setSilentDuplicatesInDifferentFiles(true)
                    ->setSilentDuplicatesInSameFile(true);

        $this->assert
                ->boolean($autoloader->autoload ('Afoo'))
                ->isTrue();


	}

	public function testAutoloaderWarningTwoSameNamespaceClassesTwoFile (){
		$autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warningnamespace2files');

		$this->assert
                ->boolean($autoloader->autoload ('not_exists'))
                ->isFalse();
        $this->assert
                ->error
                ->exists();

        //Silent won't raise warnings
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $autoloader->addPath(__DIR__.'/../resources/warningnamespace2files')
                    ->setSilentDuplicatesInDifferentFiles(true);

        $this->assert
                ->boolean($autoloader->autoload ('not_exists'))
                ->isFalse();
	}

    public function testUpdatedFileTimeLoader (){
        //Simple autoload with no cache
        $autoloader = new \Oktopus\Autoloader ('/tmp/UpdatedFileTimeLoader/', new \Oktopus\ClassParserForPHP5_3());
        $autoloader->addPath(__DIR__.'/../resources/nowarning/');
        $this->assert->boolean($autoloader->autoload ('foo'))->isTrue();

        usleep (100);
        touch (__DIR__.'/../resources/nowarning/foo.php');

        $autoloader = new \Oktopus\Autoloader ('/tmp/UpdatedFileTimeLoader/', new \Oktopus\ClassParserForPHP5_3());
        $autoloader->addPath(__DIR__.'/../resources/nowarning/');
        $this->assert->boolean($autoloader->autoload ('foo', true))->isTrue();//asking to check files
    }

    public function testCannotWriteInCacheFile (){
		//Makes sure the temporary files are writable
        if (file_exists('/tmp/nowriteincache2/')){
            $directory = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator('/tmp/nowriteincache2/'));
            foreach ($directory as $element){
                if (! in_array ($element->getFileName (), array ('.', '..'), true)){
                    chmod($element->getPathName(), 0700);
                }
            }
        }

		$autoloader = new \Oktopus\Autoloader ('/tmp/nowriteincache2/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/', false);
		$autoloader->includesAll ();

		//now trying to raise the exception
		$autoloader = new \Oktopus\Autoloader ('/tmp/nowriteincache2/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/', false);
		//cache should have been writen, going to make it read only
		$directory = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator('/tmp/nowriteincache2/'));
		foreach ($directory as $element){
			if (! in_array ($element->getFileName (), array ('.', '..'), true)){
				chmod($element->getPathName(), 0400);
			}
		}

        $this->assert
                ->exception(function() use($autoloader){$autoloader->includesAll ();})
                ->isInstanceOf('\Oktopus\AutoloaderException');

		//Back to writable
		$directory = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator('/tmp/nowriteincache2/'));
		foreach ($directory as $element){
			if (! in_array ($element->getFileName (), array ('.', '..'), true)){
				chmod($element->getPathName(), 0700);
			}
		}
	}

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

		$autoloader = new \Oktopus\Autoloader ('/tmp/nowriteincache/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/', false);
		chmod('/tmp/nowriteincache/', 0400);
		$this->assert
                ->exception(function () use($autoloader){$autoloader->includesAll ();})
                ->isInstanceOf('\Oktopus\AutoloaderException');
	}

    public function testKnownClasses (){
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $autoloader->addPath(__DIR__.'/../resources/nowarning/', false);

        //No class has been loaded.
        $this->assert
                ->phpArray($autoloader->getKnownClasses())
                ->isEqualTo(array())
                ->isEmpty();

        $this->assert
                ->boolean($autoloader->autoload('foo'))
                ->isTrue();

        $knownClasses = $autoloader->getKnownClasses();
        list(, $values) = each($knownClasses);

        $this->assert
                ->phpArray($values)
                ->contain('foo')
                ->contain('foo2')
                ->contain('foo3')
                ->hasSize(3);
    }

	public function testIncludesAll (){
		$autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/', false);

		//No class has been loaded.
		$this->assert
                ->phpArray($autoloader->getKnownClasses())
                ->isEmpty();
		$autoloader->includesAll();

		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
		$this->assert
                    ->phpArray($values)
                    ->contain('foo')
                    ->contain('foo2')
                    ->contain('foo3')
                    ->hasSize(3);

		$autoloader->addPath(__DIR__.'/../resources/nowarning/namespaces', false);

		//Still not known, adding a path does not trigger the autoload
		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
        $this->assert
                    ->phpArray($values)
                    ->contain('foo')
                    ->contain('foo2')
                    ->contain('foo3')
                    ->hasSize(3);

		$autoloader->autoload('foo');
		//Still not known, adding the cache should be enougth
		$knownClasses = $autoloader->getKnownClasses();
		list(, $values) = each($knownClasses);
        $this->assert
                    ->phpArray($values)
                    ->contain('foo')
                    ->contain('foo2')
                    ->contain('foo3')
                    ->hasSize(3);

		$autoloader->includesAll ();
		$knownClasses = $autoloader->getKnownClasses();
        list(, $values) = each($knownClasses);
        $this->assert
                    ->phpArray($values)
                    ->contain('foo')
                    ->contain('foo2')
                    ->contain('foo3')
                    ->hasSize(3);

		list(, $values) = each($knownClasses);
        $this->assert
                    ->phpArray($values)
                    ->contain('foo\\foo')
                    ->contain('foofoo')
                    ->contain('foo2\\foo')
                    ->contain('foo2\\foo2')
                    ->contain('foo3\\foo')
                    ->hasSize(5);
    }

	public function testAutoloaderCacheAndNoCache (){
		//Simple autoload with no cache
		$autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/');
		$this->assert->boolean($autoloader->autoload ('foo'))->isTrue();

		//Testing several cases, forcing or not the loading of the class
		$autoloader = new \Oktopus\Autoloader ('/tmp/', new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/');
		$this->assert->boolean($autoloader->autoload ('foo'))->isTrue();
		$this->assert->boolean($autoloader->autoload ('foo'))->isTrue();
		$this->assert->boolean($autoloader->autoload ('FOONOTEXISTS___'))->isFalse();

		//Testing to generate a cache file (tmp/somethingnew)
		$autoloader = new \Oktopus\Autoloader ('/tmp/'.uniqid (), new \Oktopus\ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/nowarning/');
		$this->assert->boolean($autoloader->autoload ('foo'))->isTrue();

		//Sees if the exception is raised while adding a path to look into that must exists
		$this->assert
                ->exception(function()use($autoloader){$autoloader->addPath ('AZERTYQWERTY/this/does/not/exists/or/this/is/very/very/bad_luck/'.uniqid (), true);})
                ->isInstanceOf('Oktopus\\AutoloaderException');
	}

    public function testAutoloaderSetGetCachePath()
    {
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $this->assert->variable($autoloader->getCachePath())->isNull();
        $autoloader->setCachePath('/tmp/');
        $this->assert->string($autoloader->getCachePath())->isEqualTo('/tmp/');
    }

    public function testAutoloaderSetGetSilentSameFile()
    {
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $this->assert
                    ->object($autoloader->setSilentDuplicatesInSameFile(false))
                        ->isIdenticalTo($autoloader)
                    ->boolean($autoloader->getSilentDuplicatesInSameFile())
                        ->isFalse()
                    ->object($autoloader->setSilentDuplicatesInSameFile(true))
                        ->isIdenticalTo($autoloader)
                    ->boolean($autoloader->getSilentDuplicatesInSameFile())
                        ->isTrue();
    }

    public function testAutoloaderSetGetSilentDifferentFiles ()
    {
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $this->assert
                    ->object($autoloader->setSilentDuplicatesInDifferentFiles(false))
                        ->isIdenticalTo($autoloader)
                    ->boolean($autoloader->getSilentDuplicatesInDifferentFiles())
                        ->isFalse()
                    ->object($autoloader->setSilentDuplicatesInDifferentFiles(true))
                        ->isIdenticalTo($autoloader)
                    ->boolean($autoloader->getSilentDuplicatesInDifferentFiles())
                        ->isTrue();
    }

    public function testAutoloaderSilentDefaultValues ()
    {
        $autoloader = new \Oktopus\Autoloader (null, new \Oktopus\ClassParserForPHP5_3());
        $this->assert->boolean($autoloader->getSilentDuplicatesInSameFile())
            ->isFalse();
        $this->assert->boolean($autoloader->getSilentDuplicatesInDifferentFiles())
            ->isFalse();
    }
}
