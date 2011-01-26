<?php
ini_set ('display_errors', 1);
require_once ('./Oktopus/Engine.class.php');
Oktopus\Engine::start ('/tmp/');


$iterator = new Oktopus\LambdaFilterIteratorDecorator (new ArrayIterator(array ('1', '2')));
$iterator->setLambda (function (){
		if (substr ($this->current (), -1 * strlen ('.php')) === '.php'){
	         return is_readable ($this->current ());
	    }
	    return false;
});





exit;
require_once ('./Oktopus/OktopusAutoloader.class.php');

OktopusAutoloader::register ();
OktopusAutoloader::unregister();
OktopusAutoloader::register ();
OktopusAutoloader::isRegistered ();

class OktopusConfiguration implements IOktopusLazyBuilder {
	public function configure (Oktopus $pOktopusInstance, $pInstanceName){
		
	}
}

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