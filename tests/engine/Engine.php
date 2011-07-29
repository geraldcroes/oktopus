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
	}
}