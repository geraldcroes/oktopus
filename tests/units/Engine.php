<?php
namespace Oktopus\tests\units;

require_once __DIR__ . '/../mageekguy.atoum.phar';

require_once __DIR__ . '/../../Oktopus/Exception.php';
require_once __DIR__ . '/../../Oktopus/AutoloaderException.php';
require_once __DIR__ . '/../../Oktopus/Engine.php';
require_once __DIR__ . '/../../Oktopus/Parser/ClassParser.php';
require_once __DIR__ . '/../../Oktopus/Parser/ClassParserForPhp5_3.php';
require_once __DIR__ . '/../../Oktopus/Autoloader.php';
require_once __DIR__ . '/../../Oktopus/Di/Container.php';
require_once __DIR__ . '/../../Oktopus/Di/MutableContainer.php';
require_once __DIR__ . '/../../Oktopus/Di/ContainerXmlLoader.php';
require_once __DIR__ . '/../../Oktopus/Di/BasicContainer.php';

use \mageekguy\atoum;

class Engine extends atoum\test {
	public function testCreate ()
	{
        //Should raise an exception as the autoloader won't be ready until the Parser is started
        $this->assert
                ->exception(function(){\Oktopus\Engine::autoloader();})
                ->isInstanceOf('\exception');
	}

	public function testContainer ()
	{
        \Oktopus\Engine::start();

        $this->assert
                ->object(\Oktopus\Engine::container())
                ->isInstanceOf('\Oktopus\Di\Container');

        \Oktopus\Engine::autoloader()->unregister();
	}

    public function testGetAutoloader ()
    {
        //Asserting that Parser's autoloader cannot be accessed until Parser's start up
        $this->assert
                ->exception(function(){\Oktopus\Engine::autoloader();})
                ->isInstanceOf('Exception');

        //Once started, assert that the autoloader can be accessed and has the right temporary path
        \Oktopus\Engine::start('/tmp/');
        $this->assert
                ->object(\Oktopus\Engine::autoloader())
                    ->isInstanceOf('\Oktopus\Autoloader')
                    ->isIdenticalTo(\Oktopus\Engine::autoloader());
    }
}