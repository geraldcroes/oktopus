<?php
namespace Oktopus\tests\units;

require_once __DIR__ . '/../mageekguy.atoum.phar';
require_once __DIR__.'/../../Oktopus/Engine.php';

use \mageekguy\atoum;

class Engine extends atoum\test {
	public function testCreate ()
	{
        //Should raise an exception as the autoloader won't be ready until the engine is started
        $this->assert
                ->exception(function(){\Oktopus\Engine::autoloader();})
                ->isInstanceOf('\exception');

        //should raise an exception as the second argument is not a known mode
        $this->assert
                ->exception(function(){\Oktopus\Engine::start ('/tmp/', 56756785678567856785678);})
                ->isInstanceOf('\exception');
	}

	public function testContainer ()
	{
	    \Oktopus\Engine::start('/tmp/');

        $this->assert
                ->object(\Oktopus\Engine::container())
                ->isInstanceOf('\Oktopus\IContainer');

        \Oktopus\Engine::start ('/tmp/', \Oktopus\Engine::MODE_PRODUCTION);
        $this->assert
                ->string(ini_get('display_errors'))
                ->isEqualTo('0');
        $this->assert->variable(\Oktopus\Engine::getMode())
                     ->isEqualTo(\Oktopus\Engine::MODE_PRODUCTION);
	}

    public function testGetTemporaryFilePath()
    {
        $this->assert->variable(\Oktopus\Engine::getTemporaryFilesPath())->isNull();
    }

    public function testGetAutoloader ()
    {
        //Asserting that engine's autoloader cannot be accessed until engine's start up
        $this->assert
                ->exception(function(){\Oktopus\Engine::autoloader();})
                ->isInstanceOf('Exception');

        //Once started, assert that the autoloader can be accessed and has the right temporary path
        \Oktopus\Engine::start('/tmp/');
        $this->assert
                ->object(\Oktopus\Engine::autoloader())
                ->isInstanceOf('\Oktopus\Autoloader')
                ->string(\Oktopus\Engine::autoloader()->getCachePath())
                ->isEqualTo(\Oktopus\Engine::getTemporaryFilesPath());
    }

    /**
     * Test the engine autoloader (maybe we should move this test into the Engine test class)
     */
    public function testEngineAutoloader (){
        \Oktopus\Engine::start('/tmp/');
        //Check that the engine autoloader has the Oktopus temporary files path configured
        $this->assert
                   ->string(\Oktopus\Engine::getTemporaryFilesPath ())
                   ->isEqualTo(\Oktopus\Engine::autoloader()->getCachePath ());

        //check that the engine reports changes to the temporary path...
        \Oktopus\Engine::setTemporaryFilesPath(null);
        $this->assert
                ->variable(\Oktopus\Engine::getTemporaryFilesPath ())
                ->isEqualTo(\Oktopus\Engine::autoloader()->getCachePath ());
    }

    /**
     * @return void
     */
    public function testAutoloaderDefaultSilentValues ()
    {
        \Oktopus\Engine::start('/tmp/', \Oktopus\Engine::MODE_PRODUCTION);
        //Silent should be true by default in production mode
        $this->assert
                ->variable(\Oktopus\Engine::getMode())
                ->isEqualTo(\Oktopus\Engine::MODE_PRODUCTION);
        $this->assert
                    ->boolean(\Oktopus\Engine::autoloader()->getSilentDuplicatesInSameFile())
                    ->isTrue();

        $this->assert
                    ->boolean(\Oktopus\Engine::autoloader()->getSilentDuplicatesInSameFile())
                    ->isTrue();

        //Silent should be false by default in debug mode
        \Oktopus\Engine::start('/tmp/', \Oktopus\Engine::MODE_DEBUG);
        $this->assert
                ->variable(\Oktopus\Engine::getMode())
                ->isEqualTo(\Oktopus\Engine::MODE_DEBUG);

        $this->assert
                    ->boolean(\Oktopus\Engine::autoloader()->getSilentDuplicatesInSameFile())
                    ->isFalse();

        $this->assert
                    ->boolean(\Oktopus\Engine::autoloader()->getSilentDuplicatesInSameFile())
                    ->isFalse();
    }
}