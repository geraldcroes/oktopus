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
}