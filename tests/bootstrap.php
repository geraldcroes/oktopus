<?php
require (__DIR__.'/../Oktopus/Engine.php');
Oktopus\Engine::start ('/tmp/');
Oktopus\Debug::unregisterErrorHandler();
Oktopus\Debug::unregisterExceptionHandler();