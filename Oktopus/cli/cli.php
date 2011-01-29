<?php
namespace Oktopus;

class Cli {
	public static function main (){
		return self::process ($_SERVER['argv']);		
	}
	
	public function process ($args){
		echo "Oktopus ", Engine::VERSION;
		echo "\nNo command line options for the moment.";
		echo "\n";
	}
}