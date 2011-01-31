<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);


$directory = new \RegexIterator(new \DirectoryIterator ('./tests/engine/resources/'), '/\\.php$/');
foreach ($directory as $fileName){
	echo $fileName->getPathName ();
}
exit;


require_once ('./Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/OktopusGit/43/');
Oktopus\Engine::autoloader()->addPath('/var/www/Copix_3/');

echo " chemin ajout� <br />";
new CopixDbProfile('', '', '', '', '');
echo "Instance obtenue <br />";