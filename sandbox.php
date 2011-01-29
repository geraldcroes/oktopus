<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);

require_once ('./Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/OktopusGit/4/');
Oktopus\Engine::autoloader()->addPath('/var/www/Copix_3/');

echo "chemin ajouté <br />";
new CopixDbProfile('', '', '', '', '');
echo "Instance obtenue <br />";