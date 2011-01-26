<?php
namespace Oktopus;

/**
 * Base Oktopus Exception
 *
 * @author geraldcroes
 */
class Exception extends \Exception {
	public function __construct ($pMessage, $pCode = null, $pPrevious = null){
		parent::__construct($pMessage, $pCode, $pPrevious);
		$this->message = Debug::formatErrorMessage ($pMessage);
		if (Engine::getMode () === Engine::MODE_DEBUG){
			$this->message .= Debug::trace(debug_backtrace()) .
								Debug::debug_source($this->getFile(), $this->getLine());
		}
	}
}

/**
 * Exception when objects are called in an invalid state
 *
 * @author geraldcroes
 */
class ObjectNotReadyException extends Exception {}

/**
 * Exception when wrong parameters are given to method / functions
 *
 * @author geraldcroes
 */
class WrongParameterException extends Exception {}