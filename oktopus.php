#!/usr/bin/env php
<?php
if (strpos('@php_bin@', '@php_bin') === 0) {
    set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());
}

require_once ('Oktopus/Engine.php');
Oktopus\Engine::start (null, Oktopus\Engine::MODE_PRODUCTION);
Oktopus\Cli::main ();