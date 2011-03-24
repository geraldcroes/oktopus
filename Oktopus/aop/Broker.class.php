<?php
namespace Oktopus\AOP;

class Broker
{
	public static function instance ()
	{
		return new Broker();
	}

	public function isBeforeCall ($pClassName, $pMethodName)
	{
		echo "Is there a $pClassName->$pMethodName ?";
		return true;
	}
	
	public function beforeCall ($pClassName, $pMethodName)
	{
		echo "Calling $pClassName->$pMethodName";
	}
	
	public function catchException ($pClassName, $pMethodName, $pException)
	{
		echo "Catching $pClassName->$pMethodName";
		return true;
	}
	
	public function & afterCall ($pClassName, $pMethodName, & $pReturnValue)
	{
		echo "After calling $pClassName->$pMethodName value of $pReturnValue";
		return $pReturnValue;
	}
}