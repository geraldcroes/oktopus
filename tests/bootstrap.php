<?php
require_once __DIR__ . '/mageekguy.atoum.phar';
require_once (__DIR__.'/../Oktopus/Engine.php');
Oktopus\Engine::start ('/tmp/', Oktopus\Engine::MODE_PRODUCTION);