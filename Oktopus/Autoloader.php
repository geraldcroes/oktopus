<?php
namespace Oktopus;

use Oktopus\Parser\ClassParser,
    Oktopus\Parser\ClassParserForPhp5_3,
    Oktopus\ClassCollection\DirectoryIteratorAdaptatorForClassCollection,
    Oktopus\ClassCollection\ClassCollection,
    Oktopus\ClassCollection\ClassCollectionCollection;

/**
 * Autoloader from ClassCollections
 *
 * @package Oktopus
 * @author  Gérald Croës <gerald@croes.org>
 */
class Autoloader
{
    /**
     * @var ClassCollectionCollection
     */
    protected $classCollection;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->classCollection = new ClassCollectionCollection();
    }

    /**
     * Register the autoloader to the stack
     *
     * @throws AutoloaderException if the autoloader is already registered
     *
     * @return Oktopus\Autoloader
     */
    public function register()
    {
        if ($this->isRegistered()) {
            throw new AutoloaderException('This Oktopus\Autoloader is already registered');
        } else {
            spl_autoload_register(array($this, 'autoload'));
        }
        return $this;
    }

    /**
     * Remove Autoloader from the autoload stack
     *
     * @return Oktopus\Autoloader
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'autoload'));
        return $this;
    }

    /**
     * Says if the Autoloader is registered
     *
     * @return boolean
     */
    public function isRegistered()
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
     * Adds a collection for the autoload to look into while autoloading classes
     *
     * @param ClassCollection $pCollection the collection you wish to add to the autoload stack
     *
     * @return Oktopus\Autoload
     */
    public function addClassCollection(ClassCollection $pCollection)
    {
        $this->classCollection->add($pCollection);
        return $this;
    }

    /**
     * @param $pDirectory string the directory where the phpfiles are located
     * @param $pRecursive bool   if subdirectories should be parsed
     */
    public function addPath($pDirectory, $pRecursive = true)
    {
        //recursive or not ?
        if ($pRecursive) {
            $baseIterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($pDirectory)
            );
        } else {
            $baseIterator = new \DirectoryIterator($pDirectory);
        }

        //only parsing php files.
        $baseIterator = new \RegexIterator($baseIterator, '/^.*\.php$/i');
        $this->addClassCollection($collection = new DirectoryIteratorAdaptatorForClassCollection(
                $baseIterator,
                new ClassParserForPHP5_3()
            )
        );
        $collection->setSilentDuplicatesInDifferentFiles(true);
        $collection->setSilentDuplicatesInSameFile(true);

        return $this;
    }

    /**
     * The autoload itself
     *
     * @param $pClassName the classname to autoload
     *
     * @return bool
     */
    public function autoload($className)
    {
        if (($fileName = $this->classCollection->getPath(strtolower($className))) !== null) {
           include_once $fileName;
           return true;
        }
        return false;
    }
}
