<?php
require_once ('./Oktopus/Engine.class.php');
Oktopus\Engine::start ('/tmp/OktopusGit/');
Oktopus\Autoloader::instance ()->addPath ('./Documentation/');

echo "tout va bien";