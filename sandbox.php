<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);

$pFileName = __DIR__.'/tests/engine/resources/nowarning/foo.php';
$tokens = token_get_all (file_get_contents ($pFileName, false));

print_r ($tokens);exit;

$currentNamespace = '';
$namespaceHunt = false;
$interfaceHunt = false;
$functionHunt = false;
$validatedNamespaceHunt = false;
$classHunt = false;

$brackets = array('id'=>array ('opened', 'closed', 'brackets'=>$brackets));
$lastIdentifier = null;

$toReturn = array('functions'=>array(), 'classes'=>array(), 'functions_call'=>array());
foreach ($tokens as $token){
	if (is_array ($token)){
		if ($token[0] === T_CLASS){
			$classHunt = true;
			continue;
		}elseif ($token[0] === T_INTERFACE){
			$interfaceHunt = true;
			continue;
		}elseif ($token[0] === T_NAMESPACE){
			$namespaceHunt = true;
			continue;
		}elseif ($token[0] === T_FUNCTION){
			$functionHunt = true;
			continue;
		}

		if (($classHunt || $interfaceHunt) && $token[0] === T_STRING){
			$toReturn[$classHunt ? 'classes' : 'interfaces'][] = (strlen ($currentNamespace) > 0 ? $currentNamespace.'\\' : '').$token[1];
			$classHunt = false;
			$interfaceHunt = false;
		}elseif ($functionHunt && $token[0] === T_STRING){
			$toReturn['functions'][] = (strlen ($currentNamespace) > 0 ? $currentNamespace.'\\' : '').$token[1];
			$functionHunt = false; 			
		}elseif ($namespaceHunt && $validatedNamespaceHunt && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)){
			$currentNamespace .= $token[1];
		}elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] === T_WHITESPACE){
			$currentNamespace = '';
			$validatedNamespaceHunt = true;
		}elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] !== T_WHITESPACE){
			$namespaceHunt = false;
		}elseif ($token[0] !== T_WHITESPACE){
			$lastIdentifier = $token[1];
		}
	}else{
		if ($token === ';' || $token === '{'){
			//ends the "default" namespace only 
			if ($namespaceHunt && !$validatedNamespaceHunt && $token === '{'){
				$currentNamespace = '';
			}
			$classHunt = false;
			$namespaceHunt = false;
			$validatedNamespaceHunt = false;
		}elseif ($token === '('){//that's a function call
			if (!in_array($lastIdentifier, $toReturn['functions_call'])){
				$toReturn['functions_call'][] = $lastIdentifier;
			}
		}
	}	
}
print_r($toReturn);
exit;

class ParserContext {
	private $_parent;

	public function __construct ($parent){
		$this->_parent = $parent;
	}
	
	public function inClass (){
		
	}
	
	public function isClass (){
		
	}
	
	public function inFunction (){
		
	}
	
	public function isFunction (){
		
	}
	
	public function inMethod (){
		
	}
	
	public function isMethod (){
		
	}
	
	public function inNamespace (){
		
	}
}

echo '<pre>';
echo htmlentities (var_export ($tokens, true));
echo '</pre>';
exit;

require_once ('./Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/');
Oktopus\Engine::autoloader()->addPath('/home/geraldc/workspace/Copix_3_0_X/', true, true);

new CopixDbProfile ('', '', '', '', '', '', '', '', '');