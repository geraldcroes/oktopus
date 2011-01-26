<?php
namespace Oktopus;

/**
 * FilterIterator wich accept a lambda to implement the accept method
 *
 * @author geraldcroes
 */
class LambdaFilterIteratorDecorator extends \FilterIterator {
	/**
	 * Lambda method to accept elements 
	 */
	private $_accept = null;

	/**
	 * Accept or not the current element
	 * 
	 * Uses the lambda function previously set.
	 * @throws ObjectNotReadyException
	 */
	public function accept (){
		if (!isset ($this->_accept)){
			throw new ObjectNotReadyException('The iterator is not ready, you have to set a valid callback method using setLambda ($pCallBack)');
		}
		$func = $this->_accept;
		return $func ($this);
	}

	/**
	 * Defines the lambda method "accept" of the decorator
	 *
	 * @param lamda   $pCallBack
	 * @param boolean $pCheck Says if LambdaFilterIteratorDecorator should check for your lamda function. If not and you're lamda is not correct, it may lead to Fatal Errors.
	 * @throws WrongParameterException
	 */
	public function setLambda ($pCallBack, $pCheck = true){
		try {
			$reflection = new \ReflectionFunction($pCallBack);
			if ($reflection->getNumberOfParameters() !== 1){
				throw new WrongParameterException('Given callback should accept one parameter (the filteriterator object)');
			}
			$this->_accept = $pCallBack;
		}catch (ReflectionException $e){
			throw new WrongParameterException('Given parameter is not a valid lambda');
		}
	}
}