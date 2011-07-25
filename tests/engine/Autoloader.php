<?php
namespace Oktopus\tests\units;

require __DIR__.'/../bootstrap.php';

use Oktopus\ClassParserForPHP5_3;
use Oktopus\Engine;
use \mageekguy\atoum;


class Autoloader extends atoum\test {
	/**
     * Tries to autoload a previously known class (in the cache) that has been deleted.
     * Should not raise any error
     */
    public function testAutoloadKnownClassThatHasBeenDeleted (){
		//Remove old cache file if it exists and copy sources to test
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/tmp/');
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/sources/');
		exec('cp -R '.__DIR__.'/../resources/nowarning /tmp/OktopusTest/testDeleteFile/sources/');
		
		//will generate cache file for every classes (including foo)
        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assert
                ->boolean($autoloader->autoload ('foo2'))
                ->isTrue();
		
		//will try to load foo after deleting the autoloader cache
        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
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
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/tmp/');
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/sources/');
		exec('cp -R '.__DIR__.'/../resources/nowarning /tmp/OktopusTest/testDeleteFile/sources/');

		//will generate cache file for every classes (including foo)
        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assert
                ->boolean($autoloader->autoload ('foo2'))
                ->isTrue();

		$autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
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
        \Oktopus\Engine::start ('/tmp/', \Oktopus\Engine::MODE_DEBUG);

		//Remove old cache file if it exists and copy sources to test
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/tmp/');
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/sources/');
		exec('cp -R '.__DIR__.'/../resources/nowarning /tmp/OktopusTest/testDeleteFile/sources/');

		//will generate cache file for every classes (including foo)
        $autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assert
                ->boolean($autoloader->autoload ('foo2'))
                ->isTrue();

		$autoloader = new \Oktopus\Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
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
}