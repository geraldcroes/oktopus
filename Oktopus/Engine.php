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
     * The Oktopus Version
     *
     * @var string
     */
    const VERSION = '0.1';

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
    public static function start ()
    {
        if (self::$_autoloader === false) {
            require_once __DIR__ . '/Exception.php';
            require_once __DIR__ . '/Autoloader.php';
            require_once __DIR__ . '/AutoloaderException.php';
            require_once __DIR__ . '/Parser/ClassParser.php';
            require_once __DIR__ . '/Parser/ClassParserForPhp5_3.php';
            require_once __DIR__ . '/ClassCollection/ClassCollection.php';
            require_once __DIR__ . '/ClassCollection/KnownClassCollection.php';
            require_once __DIR__ . '/ClassCollection/DirectoryIteratorAdaptatorForClassCollection.php';

            self::$_autoloader = new Autoloader();
            self::$_autoloader->addPath(__DIR__, true)->register();
        }
    }

    /**
     * The Parser Autoloader instance
     * 
     * @var Autoloader
     */
    private static $_autoloader = false;

    /**
     * The Oktopus Container
     *
     * @var Container
     */
    private static $_container = false;

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
}