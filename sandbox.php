<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);

require_once ('./Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/');
Oktopus\Engine::autoloader()->addPath('/home/geraldc/workspace/Copix_3_0_X/', true, true);

new CopixDbProfile ('', '', '', '', '', '', '', '', '');

