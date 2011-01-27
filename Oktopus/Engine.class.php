<?php
namespace Oktopus;

define ('OKTOPUS_PATH', __DIR__.'/');

/**
 * Main base class for Oktopus
 * 
 * @author geraldcroes
 */
class Engine {
	/**
	 * The DEBUG mode of Oktopus
	 */
	const MODE_DEBUG = 0;
	/**
	 * The production mode of Oktopus
	 */
	const MODE_PRODUCTION = 1;
	
	/**
	 * The charset of Oktopus
	 */
	public static $charset = 'utf-8';

	/**
	 * Configured mode for Oktopus
	 * @see Oktopus\Engine::MODE_DEBUG
	 * @see Oktopus\Engine::MODE_PRODUCTION 
	 */
	private static $_mode; 
	
	/**
	 * Includes the base class for Oktopus
	 */
	public static function start ($pTmpPath, $pMode = self::MODE_DEBUG){
		if (!in_array ($pMode, array (self::MODE_DEBUG, self::MODE_PRODUCTION))){
			require_once (OKTOPUS_PATH.'engine/exception/Exception.class.php');
			throw new WrongParameterException('Unknown start mode, you can start the engine using Oktopus\Engine::MODE_DEBUG or Oktopus\Engine::MODE_PRODUCTION');
		}else{
			self::$_mode = $pMode;
		}
		
		if ($pMode === self::MODE_DEBUG){
			ini_set ('display_errors', 1);
			error_reporting (E_ALL | E_STRICT);
		}

		require_once (OKTOPUS_PATH.'engine/autoloader/Autoloader.class.php');
		Autoloader::instance ()->setCachePath ($pTmpPath)->addPath (OKTOPUS_PATH, true)->register ();
		
		if ($pMode === self::MODE_DEBUG && Autoloader::instance ()->autoload ('Oktopus\\Debug')){
			Debug::register_error_handler();
			Debug::register_exception_handler();
		}
	}

	/**
	 * Gets the configured mode for Oktopus
	 * @see Oktopus\Engine::MODE_DEBUG
	 * @see Oktopus\Engine::MODE_PRODUCTION
	 * @see Oktopus\Engine::start ();  
	 */
	public static function getMode (){
		return self::$_mode;	
	}
}