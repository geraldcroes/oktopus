<?php
/**
 * Main Oktopus class.
 * 
 * @author geraldc
 */
class Publisher {
	/**
	 * Default instance name while calling create
	 * 
	 * @var string
	 */
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
			Roots::builder ('oktopus')->configure (self::$_ocean[$pName], $pName);
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