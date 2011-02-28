<?php
namespace Oktopus;


class Cli {
    private $_commands = array();
    
	public function main (){
	    $cli = new Cli ();
	    $cli->process($_SERVER['argv']);
	}
	
	public function process ($args){
		echo "Oktopus ", Engine::VERSION;
		echo "\nby Gérald Croës\n";
		
		foreach ($args as $key=>$argument) {
		    if ($argument === '-a') {
		        if (!array_key_exists($key+1, $args)){
		            $this->_errorArgument('-a Expects an argument (directory or filename)');
		        } elseif (!is_readable($args[$key+1])) {
                    $this->_errorArgument('-a Expects to be given a readable directory or a filename');		            		            
		        } else {
		            $this->_stackCommand('analyze', $args[$key+1]);
		        }
		    }
		}
		
		$this->_processCommands();
	}
	
	private function _stackCommand ($type, $arguments) {
	    if (!isset($this->_commands[$type])) {
	        $this->_commands[$type] = array();
	    }
	    
	    $this->_commands[$type][] = $arguments;
	}
	
	private function _processCommands () {
	    foreach ($this->_commands as $type=>$args) {
	        switch ($type) {
	            case 'analyze':
	                require_once 'Oktopus/sandbox/CodeParser.class.php';
	                $codeParser = new CodeParser();
	                print_r($codeParser->analyse($args[0]));
	        }
	    }
	}
}