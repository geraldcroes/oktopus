<?php
//Weird bug, fixing it including PHPUnit/Runner/Version
require_once 'PHPUnit/Runner/Version.php';

require (__DIR__.'/../Oktopus/Engine.php');
Oktopus\Engine::start ('/tmp/');
Oktopus\Debug::unregisterErrorHandler();
Oktopus\Debug::unregisterExceptionHandler();