<?php
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