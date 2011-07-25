<?php
require __DIR__ . '/mageekguy.atoum.phar';
require (__DIR__.'/../Oktopus/Engine.php');
Oktopus\Engine::start ('/tmp/', Oktopus\Engine::MODE_PRODUCTION);
Oktopus\Debug::unregisterErrorHandler();
Oktopus\Debug::unregisterExceptionHandler();