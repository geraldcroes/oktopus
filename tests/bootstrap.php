<?php
require (__DIR__.'/../Oktopus/Engine.php');
Oktopus\Engine::start ('/tmp/');
Oktopus\Debug::unregister_error_handler();
Oktopus\Debug::unregister_exception_handler();