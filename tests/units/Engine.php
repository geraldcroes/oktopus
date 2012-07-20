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

class Engine extends atoum\test
{
    public function testContainer()
    {
        \Oktopus\Engine::autoloader()->register();

        $this->assert
            ->object(\Oktopus\Engine::container())
            ->isInstanceOf('\Oktopus\Di\Container');

        \Oktopus\Engine::autoloader()->unregister();
    }

    public function testGetAutoloader()
    {
        \Oktopus\Engine::autoloader()->register();
        $this->assert
            ->object(\Oktopus\Engine::autoloader())
            ->isInstanceOf('\Oktopus\Autoloader')
            ->isIdenticalTo(\Oktopus\Engine::autoloader());
    }
}