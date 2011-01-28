<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);

require_once ('./Oktopus/Engine.class.php');
Oktopus\Engine::start ('/tmp/OktopusGit/3/');
Oktopus\Autoloader::instance ()->addPath ('/var/www/Copix_3/');

echo "chemin ajouté <br />";
new CopixDbProfile ('', '', '', '', '');
echo "Instance obtenue <br />";