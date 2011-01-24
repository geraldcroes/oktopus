<?php
/**
 * Interface for Oktopus subscribers
 * 
 * @author geraldc
 */
interface IOktopusSubscriber {
	/**
	 * Gets all the event names the subscriber wants to follow 
	 */
	public function getSubscribedEvents ();

	/**
	 * The subscriber is called with the event he's listening to 
	 */
	public function notify (OktopusEvent $pEvent);//notification de tous les éléments écoutés

	/**
	 * Tells if the subscriber needs to be called directly after the event or if he can wait later 
	 */
	public function isAssynchronous ();//indique si le subscriber peut être 
}

/**
 * Base Oktopus Exception
 * 
 * @author geraldc
 */
class OktopusException extends Exception {}

/**
 * Main Oktopus class.
 * 
 * @author geraldc
 */
class Oktopus {
	const DEFAULT_OKTOPUS_NAME = 'default';

	/**
	 * All instances of Oktopus are stored in this table
	 * 
	 * @var array of Oktopus
	 */
	private static $_ocean = array ();

	/**
	 * List of subscribers  
	 * 
	 * @var array of [string] = array of subscriber
	 */
	private $_arms = array ();

	/**
	 * Create an Oktopus object
	 * 
	 * @param string $pName name of the Oktopus
	 * @throws OktopusException
	 */
	public static function create ($pName = self::DEFAULT_OKTOPUS_NAME){
		if (!is_string ($pName)){
			throw new OktopusException ('Oktopus names must be Strings');
		}

		if (array_key_exists ($pName, self::$_ocean)){
			self::$_ocean[$pName] = new Oktopus ();
		}

		return self::$_ocean[$pName];
	}

	/**
	 * Adds an event to an Oktopus object
	 * 
	 * @param OktopusEvent $pEvent
	 */
	public function notify (OktopusEvent $pEvent){
		foreach ($this->_arms[$pEvent->getName ()] as $sub){
			$sub->notify ($pEvent);
		}
	}

	/**
	 * Adds a subscriber to Oktopus
	 * 
	 * @param IOktopusSubscriber $pSubscriber
	 */
	public function subscribe (IOktopusSubscriber $pSubscriber){
		foreach ($pSubscriber->getSubscribedEvents () as $eventName){
			if (! array_key_exists ($eventName, $this->_arms)){
				$this->_arms[$eventName] = array ();
			}
			$this->_arms[$eventName][] = $pSubscriber;
		}
	}
}

/**
 * This is base class for Events in Oktopus
 * 
 * @author geraldc
 */
class OktopusEvent {
	/**
	 * Name of the event.
	 * 
	 * @var string
	 */
	private $_name;

	/**
	 * Parameters for the event
	 * 
	 * @var array
	 */
	private $_parameters;

	/**
	 * Constructor
	 * 
	 * @param string $pName
	 * @param array  $pParameters
	 */
	public function __construct ($pName, $pParameters){
		$this->_name = $pName;
		$this->_parameters = $pParameters;
	}
	
	/**
	 * Magic method to read name & parameters
	 * 
	 * @param string $pName
	 * @throws OktopusException in case you're reading a non existing property
	 */
	public function __get ($pName){
		switch ($pName){
			case 'name': return $this->_name;
			case 'parameters': return $this->_parameters;
			default: throw new OktopusException("Parameter [$pName] does not exists in OktopusEvent");
		}
	}
}