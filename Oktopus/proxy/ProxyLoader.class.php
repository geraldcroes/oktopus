<?php
namespace Oktopus;

class ProxyLoader
{
	public function load ($pProxiedName)
	{
		if (is_readable($filePath = \Oktopus\Engine::getTemporaryFilesPath().str_replace('/', '_', $pProxiedName))) {
			echo file_get_contents($filePath);
			include $filePath;
		} else {
			$generator = new ProxyGenerator();
			file_put_contents($filePath, $generator->generate($pProxiedName));
			include $filePath;
		}
		$className = $this->_getProxyName($pProxiedName);
		return new $className();
	}
		
	private function _getProxyName ($pName)
	{
		return 'Proxy'.$pName;
	} 
}