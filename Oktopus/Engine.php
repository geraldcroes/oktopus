<?php
namespace Oktopus;

define ('OKTOPUS_PATH', __DIR__.'/');

/**
 * Base Oktopus Exception
 *
 * @author geraldcroes
 */
class Exception extends \Exception {}

/**
 * Main base class for Oktopus
 * 
 * @author geraldcroes
 */
class Engine {
	/**
	 * The RESET mode of Oktopus (delete every cache files on initialization)
	 */
	const MODE_RESET = -1;

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
		if (!in_array ($pMode, array (self::MODE_DEBUG, self::MODE_PRODUCTION, self::MODE_RESET))){
			require_once (OKTOPUS_PATH.'engine/exception/Exception.class.php');
			throw new WrongParameterException('Unknown start mode, you can start the engine using Oktopus\Engine::MODE_DEBUG or Oktopus\Engine::MODE_PRODUCTION');
		}else{
			self::$_mode = $pMode;
		}
		
		if ($pMode === self::MODE_RESET){
			rmdir($pTmpPath);
		}
		
		if ($pMode === self::MODE_DEBUG){
			ini_set ('display_errors', 1);
			error_reporting (E_ALL | E_STRICT);
		}

		require_once (OKTOPUS_PATH.'engine/codeparser/ClassParserForPHP.php');
		require_once (OKTOPUS_PATH.'engine/autoloader/Autoloader.php');
		self::$_autoloader = new Autoloader ('/tmp/', new ClassParserForPHP5_3());
		self::$_autoloader->addPath (OKTOPUS_PATH, true)->register ();
	}

	private static $_autoloader = false;
	public static function autoloader (){
		return self::$_autoloader;
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