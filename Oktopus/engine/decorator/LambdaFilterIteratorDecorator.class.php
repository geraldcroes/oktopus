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
	private $_accept;

	/**
	 * Accept or not the current element
	 * 
	 * Uses the lambda function previously set.
	 * @throws ObjectNotReadyException
	 */
	public function accept (){
		if (!isset ($this->$_accept)){
			throw new ObjectNotReadyException('The iterator is not ready, you have to set a valid callback method using setLambda ($pCallBack)');
		}
		return $this->$_accept();
	}

	/**
	 * Defines the lambda method "accept" of the decorator
	 *
	 * @param lamda $pCallBack
	 * @throws WrongParameterException
	 */
	public function setLambda ($pCallBack){
		if (! is_callable($pCallBack)){
			throw new WrongParameterException('Given parameter is not a valid callback method');
		}
	}
}