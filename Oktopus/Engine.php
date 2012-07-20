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

require_once __DIR__ . '/Exception.php';
require_once __DIR__ . '/Autoloader.php';
require_once __DIR__ . '/AutoloaderException.php';
require_once __DIR__ . '/Parser/ClassParser.php';
require_once __DIR__ . '/Parser/ClassParserForPhp5_3.php';
require_once __DIR__ . '/ClassCollection/ClassCollection.php';
require_once __DIR__ . '/ClassCollection/KnownClassCollection.php';
require_once __DIR__ . '/ClassCollection/ClassCollectionCollection.php';
require_once __DIR__ . '/ClassCollection/DirectoryIteratorAdaptatorForClassCollection.php';

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
     * The Parser Autoloader instance
     * 
     * @var Autoloader
     */
    private static $autoloader = false;

    /**
     * The Oktopus Container
     *
     * @var Container
     */
    private static $container = false;

    /**
     * Gets the Engine Autoloader instance
     * 
     * @return Autoloader
     */
    public static function autoloader ()
    {
        if (self::$autoloader === false) {
            self::$autoloader = new Autoloader();
            self::$autoloader->addPath(__DIR__, true);
        }
        return self::$autoloader;
    }
    
    /**
     * Gets the Oktopus Container
     * 
     * @return Container
     */
    public static function container ()
    {
        if (self::$container === false) {
            self::$container = new ContainerXMLLoader(new BasicContainer());
        }
        return self::$container;
    }
}
