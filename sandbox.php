<?php
require_once ('./Oktopus/Engine.class.php');
Oktopus\Engine::start ('/tmp/Oktopus/');
Oktopus\Autoloader::instance ()->addPath ('./Documentation/');

$test = $test +1;

echo "tout va bien";