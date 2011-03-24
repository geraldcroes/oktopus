<?php
namespace Oktopus;

class DecoratorLoader
{
	public function load ($pDecoratedName)
	{
		if (is_readable($filePath = \Oktopus\Engine::getTemporaryFilesPath().str_replace('/', '_', $pDecoratedName))) {
			echo file_get_contents($filePath);
			include $filePath;
		} else {
			$generator = new DecoratorGenerator();
			file_put_contents($filePath, $generator->generate($pDecoratedName));
			include $filePath;
		}
		$className = $this->_getDecoratorName($pDecoratedName);
		return new $className();
	}
		
	private function _getDecoratorName ($pName)
	{
		return 'Decorator'.$pName;
	} 
}