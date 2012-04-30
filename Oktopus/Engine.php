<?php
/**
 * Oktopus Engine
 * 
 * @author    Gérald Croës <gerald@croes.org>
 * @copyright 2010-2011 Gérald Croës <gerald@croes.org>
 * @license   GNU Lesser General Public License see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 * @version   $Id$
 * @link      http://www.oktopus-project.org
 */
namespace Oktopus;

use Oktopus\Parser\ClassParser,
    Oktopus\Parser\ClassParserForPhp5_3,
    Oktopus\Di\ContainerXMLLoader,
    Oktopus\Di\BasicContainer;

/**
 * Main base class for Oktopus
 * 
 * @package Oktopus
 * @author  Gérald Croës <gerald@croes.org>
 */
class Engine
{
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
     * The Oktopus Version
     *
     * @var string
     */
    const VERSION = '0.1';

    /**
     * The charset of Oktopus
     */
    public static $charset = 'utf-8';

    /**
     * Configured mode for Oktopus
     * 
     * @see Oktopus\Engine::MODE_DEBUG
     * @see Oktopus\Engine::MODE_PRODUCTION
     */
    private static $_mode;
    
    /**
     * Includes the base class for Oktopus
     * 
     * In debug mode, Oktopus will add its own error and exception handlers.
     * 
     * @param string $pTmpPath the temporary path
     * @param int    $pMode    the mode the Parser will be in (Engine::MODE_DEBUG,
     *                         Engine::MODE_PRODUCTION), default is DEBUG
     *
     * @throws \InvalidArgumentException
     *
     * @see Oktopus\Engine::MODE_PRODUCTION
     * @see Oktopus\Engine::MODE_DEBUG
     * @see Oktopus\Engine::MODE_RESET
     * @see Oktopus\Debug
     */
    public static function start ($pTmpPath, $pMode = self::MODE_DEBUG)
    {
        require_once __DIR__ . '/Exception.php';
        require_once __DIR__ . '/Autoloader.php';
        require_once __DIR__ . '/AutoloaderException.php';
        require_once __DIR__ . '/Parser/ClassParser.php';
        require_once __DIR__ . '/Parser/ClassParserForPhp5_3.php';

        if (!in_array($pMode, array(self::MODE_DEBUG, self::MODE_PRODUCTION, self::MODE_RESET))) {
            throw new \InvalidArgumentException(
                'Unknown start mode, you can start the Parser using Oktopus\Engine::MODE_DEBUG '.
                'or Oktopus\Engine::MODE_PRODUCTION'
            );
        } else {
            self::$_mode = $pMode;
        }

        self::$_temporaryFilesPath = $pTmpPath;

        self::$_autoloader = new Autoloader($pTmpPath, new ClassParserForPHP5_3());
        self::$_autoloader->addPath(__DIR__, true)->register();
    }

    /**
     * The temporary files path
     * 
     * @var string
     */
    private static $_temporaryFilesPath;

    /**
     * Gets the Temporary Files path
     * 
     * @return string
     */
    public static function getTemporaryFilesPath ()
    {
        return self::$_temporaryFilesPath;
    }

    /**
     * Sets the temporary files path
     *
     * @param string $pTmpPath the path to set
     */
    public static function setTemporaryFilesPath ($pTmpPath)
    {
        self::$_temporaryFilesPath = $pTmpPath;
        self::autoloader()->setCachePath($pTmpPath);
    }

    /**
     * The Parser Autoloader instance
     * 
     * @var Autoloader
     */
    private static $_autoloader = false;

    /**
     * Gets the Engine Autoloader instance
     * 
     * @return Autoloader
     */
    public static function autoloader ()
    {
        if (self::$_autoloader === false) {
            throw new AutoloaderException (
                'The Engine Autoloader is not ready, you have to call Oktopus\\Engine::start () before'
            );
        }
        return self::$_autoloader;
    }
    
    /**
     * The Oktopus Container
     * 
     * @var Container
     */
    private static $_container = false;

    /**
     * Gets the Oktopus Container
     * 
     * @return Container
     */
    public static function container ()
    {
        if (self::$_container === false) {
            self::$_container = new ContainerXMLLoader(new BasicContainer());
        }
        return self::$_container;
    } 

    /**
     * Gets the configured mode for Oktopus
     * 
     * @see Engine::MODE_DEBUG
     * @see Engine::MODE_PRODUCTION
     * @see Engine::MODE_RESET
     * @see Engine::start ();
     * 
     * @return int
     */
    public static function getMode ()
    {
        return self::$_mode;
    }
}