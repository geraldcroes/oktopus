<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);

require_once ('./Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/');