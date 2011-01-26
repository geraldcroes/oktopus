<?php
require_once ('./Oktopus/Engine.class.php');
Oktopus\Engine::start ('/tmp/Oktopus/');
Oktopus\Autoloader::instance ()->addPath ('./Documentation/');

throw new Exception ('test');


echo "tout va bien";