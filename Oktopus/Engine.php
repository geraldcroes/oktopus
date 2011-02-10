<?php
/**
 * Oktopus Engine
 * 
 * @author "Gérald Croës <gerald@croes.org>"
 * @copyright 2010-2011 Gérald Croës <gerald@croes.org>
 * @link http://www.oktopus-project.org
 * @license GNU Lesser General Public License see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Oktopus;

/**
 * Base Oktopus Exception
 */
class Exception extends \Exception
{
}

/**
 * Interface for class parsing
 */
interface IClassParser
{
    /**
     * Algorithm to find classes in a given file
     * 
     * @param string $pFileName the filename to inspect
     */
    public function find($pFileName);
}

/**
 * Algorithm to find classes in a given file
 *
 * <code>
 * <?php
 *    $parser = new CodeParserForPHP5_3();
 *    $classes = $parser->find('/path/to/file/name.php');
 *    //$classes is now an array with every classes and interfaces declared in name.php
 * ?>
 * </code>
 *
 * @see IClassParser
 */
class ClassParserForPHP5_3 implements IClassParser
{
    /**
     * Find classes in $pFileName
     *
     * @param string $pFileName the filename to inspect
     * 
     * @return array
     */
    public function find ($pFileName)
    {
        $toReturn = array();
        $tokens = token_get_all(file_get_contents($pFileName, false));

        $currentNamespace = '';
        $namespaceHunt = false;
        $validatedNamespaceHunt = false;
        $classHunt = false;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_INTERFACE || $token[0] === T_CLASS) {
                    $classHunt = true;
                    continue;
                } elseif ($token[0] === T_NAMESPACE) {
                    $namespaceHunt = true;
                    continue;
                }
                if ($classHunt && $token[0] === T_STRING) {
                    $toReturn[] = (strlen($currentNamespace) > 0 ? $currentNamespace.'\\' : '').$token[1];
                    $classHunt = false;
                } elseif ($namespaceHunt && $validatedNamespaceHunt 
                          && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                    $currentNamespace .= $token[1];
                } elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] === T_WHITESPACE) {
                    $currentNamespace = '';
                    $validatedNamespaceHunt = true;
                } elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] !== T_WHITESPACE) {
                    $namespaceHunt = false;
                }
            } else {
                if ($token === ';' || $token === '{') {
                    //ends the "default" namespace only
                    if ($namespaceHunt && !$validatedNamespaceHunt && $token === '{') {
                        $currentNamespace = '';
                    }
                    $classHunt = false;
                    $namespaceHunt = false;
                    $validatedNamespaceHunt = false;
                }
            }
        }
        return $toReturn;
    }
}

/**
 * Base exception for Autolaoding errors
 */
class AutoloaderException extends Exception
{
}

/**
 * Main Oktopus Autoloader
 */
class Autoloader
{
    /**
     * Construct
     * 
     * @param string       $pTmpPath     the path where to store the cache files
     * @param ICLassParser $pClassParser the class parser to find classes in PHPFiles.
     */
    public final function __construct ($pTmpPath, IClassParser $pClassParser)
    {
        $this->_classHunter = $pClassParser;
        $this->setCachePath($pTmpPath);
    }

    /**
     * Gets the configured cache path
     *
     * @return string
     */
    public function getCachePath ()
    {
        return $this->_cachePath;
    }

    /**
     * The class parser Oktopus will use
     * @var IClassParser
     */
    private $_pClassParser;

    /**
     * Register the autoloader to the stack
     * 
     * @throws Exception if the autoloader is already registered
     * 
     * @return Autoloader
     */
    public function register ()
    {
        if ($this->isRegistered()) {
            throw new AutoloaderException('Oktopus\Autoloader is already registered');
        } else {
            spl_autoload_register(array($this, 'autoload'));
        }
        return $this;
    }

    /**
     * Remove Autoloader from the autoload stack
     */
    public function unregister ()
    {
        spl_autoload_unregister(array($this, 'autoload'));
    }

    /**
     * Says if the Autoloader is registered
     * 
     * @return boolean
     */
    public function isRegistered ()
    {
        if (($stack = spl_autoload_functions()) !== false) {
            foreach ($stack as $autoloadDescription) {
                if (is_array($autoloadDescription)) {
                    if (isset($autoloadDescription[0]) && $autoloadDescription[0] === $this) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * The cache path on the file system to store the previously parsed classes
     *
     * @var string
     */
    private $_cachePath;

    /**
     * Defines the cache where Oktopus\Autoload can write cache files
     *
     * @param string $pTmp the path where to store cache files
     * 
     * @throws AutoloaderException if the given path is not writable
     * 
     * @return Autoloader
     */
    public function setCachePath ($pTmp)
    {
        if ($pTmp !== null) {
            if (! file_exists($pTmp)) {
                if (@mkdir($pTmp, 0755, true) === false) {
                    throw new AutoloaderException('Cannot create the given CachePath ['.$pTmp.']');
                }
            } elseif (!is_writable($pTmp)) {
                throw new AutoloaderException('Cannot write in given CachePath directory ['.$pTmp.']');
            }
        }
        $this->_cachePath = ($pTmp !== null && substr($pTmp, -1) !== '/') ? $pTmp.'/' : $pTmp;
        return $this;
    }

    /**
     * Known classes by directories
     *
     * @var array
     */
    private $_directoryClasses = array ();

    /**
     * Loads the Directory classes
     * 
     * Check if the cache file exists, then check its content (debug mode) and compiles if needed
     *
     * @param string  $pDirectoryName the directory name we want to compile
     * @param boolean $pRecurse       if we want to find files recursively into the given path
     * @param boolean $pForce         if true, will look for files and compile every data without checking 
     *                                the cache files.
     * @param boolean $pCheckFiles    if true, will check the MTime of each files to know if we 
     *                                should re-check for its classes, and will check for new / removed files.
     */
    private function _loadDirectoryClasses ($pDirectoryName, $pRecurse, $pForce = false, $pCheckFiles = true)
    {
        ///Can we find the directory index ?
        $directoryIndex = array();
        $listHasChanged = false;

        //If we did not asks to force the autoload to look for classes, we'll include the cache if it exists
        $cacheFileName = $this->_makeFileName($pDirectoryName, $pRecurse);
        if ($pForce === false && is_readable($cacheFileName)) {
            require $cacheFileName;
            $this->_directoryClasses[$pDirectoryName] = $allClasses;

            //we'll get directoryIndex in the included file
            //we'll also get the classes by files
            if ($pCheckFiles === false) {
                //We were asked to just load the file if it exists.... exiting
                return;
            }
        }

        //Prepare the iterator to compile all the directory classes
        if ($pRecurse) {
            $directories = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($pDirectoryName)
                ), '/\\.php$/'
            );
        } else {
            $directories = new \RegexIterator(new \DirectoryIterator($pDirectoryName), '/\\.php$/');
        }

        //We iterate in the directories to find its files...
        foreach ($directories as $fileName) {
            if (!array_key_exists($fileName->getPathName(), $directoryIndex)) {
                //The file is not registered in the directory index, we have to analyze the new file
                $haveToAnalizeFile = true;
            } elseif ($directoryIndex[$fileName->getPathName()] < $fileName->getMTime()) {
                //The file is not up to date, we have to analyze.
                $haveToAnalizeFile = true;
            } else {
                //The file is up to date and is in the directoryIndex
                $haveToAnalizeFile = false;
            }

            //So we have to analyze the file ?
            if ($haveToAnalizeFile) {
                $directoryIndex[$fileName->getPathName()] = $fileName->getMTime();
                $classes[$fileName->getPathName()]= $this->_classHunter->find($fileName->getPathName());
                $analyzedFiles[$fileName->getPathName()] = true;
                $listHasChanged = true;
            } else {
                $analyzedFiles[$fileName->getPathName()] = false;
            }
        }

        //if we had to check the content of the index, we'll now have to iterate through the old index to find
        // if there are no missing classes.
        $toRemoveFiles = array();
        foreach ($directoryIndex as $filePathName=>$fileMTime) {
            //The file has just been checked or is up to date ?
            if (isset ($analyzedFiles[$filePathName])) {
                //nothing to do, the file was found in the directories
                continue;
            }

            //the file does not exists anymore ? (only reason or it would have been in the iterated elements)
            $toRemoveFiles[] = $filePathName;
        }

        //We're gonna remove old files from classes.
        foreach ($toRemoveFiles as $fileName) {
            if (isset ($classes[$fileName])) {
                $listHasChanged = true;
                unset ($classes[$fileName]);
            }
        }

        //Nothing changed.... ne need to go further
        if (! $listHasChanged) {
            return;
        }

        //now we're gonna make a direct access array to get the files.
        $allClasses = array ();
        foreach ($classes as $fileName=>$classesInFileName) {
            foreach ($classesInFileName as $className) {
                $className = strtolower($className);
                if (isset($allClasses[$className])) {
                    if (is_array($allClasses[$className])) {
                        if (!in_array($fileName, $allClasses[$className], true)) {
                            $allClasses[$className][] = $fileName;
                        } else {
                            trigger_error(
                                "The class $className was found twice or more in the file ".
                                "$fileName (PHP may trigger a FATAL ERROR while loading the file)", E_USER_WARNING
                            );
                        }
                    } else {
                        if ($allClasses[$className] !== $fileName) {
                            $allClasses[$className] = array($allClasses[$className], $fileName);
                        } else {
                            trigger_error(
                                "The class $className was found twice or more in the file ".
                                "$fileName (PHP may trigger a FATAL ERROR while loading the file)", E_USER_WARNING
                            );
                        }
                    }
                } else {
                    $allClasses[$className] = $fileName;
                }
            }
        }

        //Will adress warnings if a class was found in multiple files.
        foreach ($allClasses as $className=>$files) {
            if (is_array($files)) {
                $countFiles = count($files);
                trigger_error(
                    "The class $className was found in $countFiles different files ".
                    implode(', ', $files) .", the Oktopus Autoloader will use the ".
                    "first file while autoloading the Object", E_USER_WARNING
                );
            }
        }

        $this->_saveInCache($directoryIndex, $classes, $allClasses, $cacheFileName);
        $this->_directoryClasses[$pDirectoryName] = $allClasses;
    }

    /**
     * Makes the cache file name for the given directory
     *
     * @param string $pDirectoryName
     * @param string $pRecurse
     * 
     * @return null|string null if no cachePath was given, or a filepath for the cache file 
     */
    private function _makeFileName ($pDirectoryName, $pRecurse)
    {
        if ($this->_cachePath !== null) {
            return $this->_cachePath.'autoload/'.($pRecurse ? '_R_' : '' ).
                   substr(realpath($pDirectoryName).'index.php', 1);
        }
        return null;
    }

    /**
     * Saves the classes in the cache path
     * 
     * If the cache file cannot be created, will launch an exception
     * 
     * @param array  $directoryIndex  the path contained in the directory
     * @param array  $classes         classes by files $classes[filename] = array of classes
     * @param array  $allClasses      all classes by names $classname[name] = array of files 
     * @param string $fileName        the file name we will write the cache data in      
     *
     * @throws AutoloaderException
     */
    private function _saveIncache ($directoryIndex, $classes, $allClasses, $fileName)
    {
        if ($fileName !== null) {
            $toSave = '<?php $classes = '.var_export($classes, true).';';
            $toSave .= '$allClasses = '.var_export($allClasses, true).';';
            $toSave .= '$directoryIndex = '.var_export($directoryIndex, true).';';

            if (!file_exists(dirname($fileName))) {
                if (@mkdir(dirname($fileName), 0755, true) === false) {
                    throw new AutoloaderException('Cannot create cache directory '.dirname($fileName));
                }
            }

            if (@file_put_contents($fileName, $toSave, true) === false) {
                throw new AutoloaderException('Cannot write cache file '.$fileName);
            }
        }
    }

    /**
     * Trys to find a class
     * 
     * @param string $pClassName the class name to find
     * 
     * @return boolean (the class was found true, or not false)
     */
    public function autoload ($pClassName, $pAnalyzeChangedFiles = false)
    {
        $pClassName = strtolower($pClassName);
        foreach ($this->_directories as $name=>$recurse) {
            if (isset ($this->_directoryClasses[$name])
                && isset ($this->_directoryClasses[$name][$pClassName])) {
                if (! $this->_includeDirectoryClass($name, $pClassName)) {
                    //We couldn't include the class (maybe the file was deleted....
                    //We have to compile the file again.
                    $this->_loadDirectoryClasses($name, $recurse, true);
                    if ($this->_includeDirectoryClass($name, $pClassName)) {
                        //founded, return
                        return true;
                    }//else we continue..... maybe the class is in another directory now
                } else {
                    return true;
                }
            } else {
                $this->_loadDirectoryClasses($name, $recurse, false, $pAnalyzeChangedFiles);
                //we check if the class has been found
                if ($this->_includeDirectoryClass($name, $pClassName)) {
                    //founded, return
                    return true;
                }
            }
        }

        //did not find the class.... if in developpment, we'll redo the autoload
        //asking for an analyzing of files in the directories.
        //In a production environnement, we want the autoloader to trust its cached files
        // (to avoid the situation where multiple autoloaders check every files....)
        if ((!$pAnalyzeChangedFiles) && (Engine::getMode() !== Engine::MODE_PRODUCTION)) {
            return $this->autoload($pClassName, true);
        }

        //there are no class that match at all
        return false;
    }

    /**
     * We call this method when we are absolutely sure that
     *
     * @param string $pDirectory the path of the directory where the class should exist
     * @param string $pClassName the classname to include
     * 
     * @return boolean
     */
    private function _includeDirectoryClass ($pDirectory, $pClassName)
    {
        if (! isset ($this->_directoryClasses[$pDirectory][$pClassName])) {
            return false;
        }
        if (is_array($this->_directoryClasses[$pDirectory][$pClassName])) {
            $classFile = $this->_directoryClasses[$pDirectory][$pClassName][0];
        } else {
            $classFile = $this->_directoryClasses[$pDirectory][$pClassName];
        }
        if (is_readable($classFile)) {
            include_once $classFile;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds a path to look for classes
     * 
     * @param string  $pDirectory  where to look for php files
     * @param boolean $pRecursive  if we'll look recursively into the class tree
     * @param boolean $pMustExists if addPath must check the existence of the directory or not.
     * 
     * @throws AutoloaderException If $pMustExists and the directory is not readable
     *  
     * @return Oktopus\Autoloader
     */
    public function addPath ($pDirectory, $pRecursive = true, $pMustExists = true)
    {
        if (! is_readable($pDirectory) && $pMustExists) {
            //The directory must exists. Raise an exception.
            throw new AutoloaderException('Cannot read from ['.$pDirectory.']');
        } else {
            $this->_directories[$pDirectory] = $pRecursive ? true : false;
            return $this;
        }
    }

    /**
     * Gets the known classes
     * 
     * @return array
     */
    public function getKnownClasses ()
    {
        $toReturn = array();
        foreach ($this->_directoryClasses as $fileName=>$classes) {
            if (count($classes)) {
                $toReturn[$fileName] = array_keys($classes);
            }
        }
        return $toReturn;
    }

    /**
     * Includes every classes the autoloader may know
     */
    public function includesAll ()
    {
        foreach ($this->_directories as $name=>$recurse) {
            $this->_loadDirectoryClasses($name, $recurse, true);
        }
    }

    /**
     * Current path where to look for classes
     * 
     * @see Oktopus\Autoloader::addPath
     * @var array
     */
    private $_directories = array ();
}

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
     * @param int    $pMode    the mode the engine will be in (Engine::MODE_DEBUG, 
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
        if (!in_array($pMode, array(self::MODE_DEBUG, self::MODE_PRODUCTION, self::MODE_RESET))) {
            throw new \InvalidArgumentException(
                'Unknown start mode, you can start the engine using Oktopus\Engine::MODE_DEBUG '.
                'or Oktopus\Engine::MODE_PRODUCTION'
            );
        } else {
            self::$_mode = $pMode;
        }

        self::$_temporaryFilesPath = $pTmpPath;

        if ($pMode === self::MODE_DEBUG) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        } else {
            ini_set('display_errors', 0);
        }

        self::$_autoloader = new Autoloader($pTmpPath, new ClassParserForPHP5_3());
        self::$_autoloader->addPath(__DIR__, true)->register();

        if ($pMode === self::MODE_DEBUG && self::$_autoloader->autoload('Oktopus\\Debug')) {
            Debug::registerErrorHandler();
            Debug::registerExceptionHandler();
        }
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
     * The engine Autoloader instance
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