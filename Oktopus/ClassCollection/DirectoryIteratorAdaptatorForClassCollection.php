<?php
namespace Oktopus\ClassCollection;

use Oktopus\Parser\ClassParser;

/**
 * Adaptats an iterator of Files in a directory to Class Collection
 *
 * @package Oktopus
 */
class DirectoryIteratorAdaptatorForClassCollection implements KnownClassCollection
{
    /**
     * The adapted iterator
     *
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Known classes
     *
     * @var array
     */
    protected $classes;

    /**
     * If we want to waise a warning while founding twice or more the same class in the same file
     *
     * @var bool true will raise warnings, false won't raise any error
     */
    private $silentDuplicatesInSameFile = false;

    /**
     * If we want to raise a warning while founding twice or more the same class in different files
     *
     * @var bool true will raise warnings, false won't raise any error
     */
    private $silentDuplicatesInDifferentFile = false;

    /**
     * Sets if we want to raise a warning while founding twice or more the same class in the same file
     *
     * @param bool $pSilent
     *
     * @return Autoloader
     */
    public function setSilentDuplicatesInSameFile($pSilent)
    {
        $this->silentDuplicatesInSameFile = (boolean)$pSilent;
        return $this;
    }

    /**
     * Tells if it will generate a warning while founding twice or more the same class in the same file
     *
     * @return bool
     */
    public function getSilentDuplicatesInSameFile()
    {
        return $this->silentDuplicatesInSameFile;
    }

    /**
     * Sets if we want to generate a warning while founding twice or more the same class in different files
     *
     * @var bool true will generate warnings, false won't generate any error
     *
     * @return Autoloader
     */
    public function setSilentDuplicatesInDifferentFiles($pSilent)
    {
        $this->silentDuplicatesInDifferentFile = (boolean)$pSilent;
        return $this;
    }

    /**
     * Tells if it will generate a warning while founding twice or more the same class in the same file
     *
     * @return bool
     */
    public function getSilentDuplicatesInDifferentFiles()
    {
        return $this->silentDuplicatesInDifferentFile;
    }

    /**
     * Constructor
     *
     * @param \Iterator $pIterator
     */
    public function __construct(\Iterator $pIterator, ClassParser $pClassParser)
    {
        $this->iterator = $pIterator;
        $this->classParser = $pClassParser;
    }

    /**
     * @param $pName
     * @return void
     */
    public function getPath($pName)
    {
        $this->initialize();
        if (array_key_exists($pName, $this->classes)) {
            return $this->classes[$pName];
        }
        return null;
    }

    /**
     * Gets the list of known classes
     *
     * @return array
     */
    public function getKnownClasses()
    {
        $this->initialize();
        return array_keys($this->classes);
    }

    /**
     * Loads the classes in the iterator
     *
     * @return void
     */
    protected function initialize()
    {
        if (is_array($this->classes)) {
            return;
        }
        $this->loadClasses();
    }

    protected function loadClasses()
    {
        $classes = array();

        //We iterate in the directories to find its files...
        foreach ($this->iterator as $fileName) {
            $classes[$fileName->getPathName()] = $this->classParser->find($fileName->getPathName());
        }

        //now we're gonna make a direct access array to get the files.
        $this->classes = array();
        foreach ($classes as $fileName => $classesInFileName) {
            foreach ($classesInFileName as $className) {
                $className = strtolower($className);
                if (isset($this->classes[$className])) {
                    if (is_array($this->classes[$className])) {
                        if (!in_array($fileName, $this->classes[$className], true)) {
                            $this->classes[$className][] = $fileName;
                        } else {
                            if ($this->getSilentDuplicatesInSameFile() === false) {
                                trigger_error(
                                    "The class $className was found twice or more in the file " .
                                        "$fileName (PHP may trigger a FATAL ERROR while loading the file)", E_USER_WARNING
                                );
                            }
                        }
                    } else {
                        if ($this->classes[$className] !== $fileName) {
                            $this->classes[$className] = array($this->classes[$className], $fileName);
                        } else {
                            if ($this->getSilentDuplicatesInSameFile() === false) {
                                trigger_error(
                                    "The class $className was found twice or more in the file " .
                                        "$fileName (PHP may trigger a FATAL ERROR while loading the file)", E_USER_WARNING
                                );
                            }
                        }
                    }
                } else {
                    $this->classes[$className] = $fileName;
                }
            }
        }

        //Will adress warnings if a class was found in multiple files.
        if ($this->getSilentDuplicatesInDifferentFiles() === false) {
            foreach ($this->classes as $className => $files) {
                if (is_array($files)) {
                    $countFiles = count($files);
                    trigger_error(
                        "The class $className was found in $countFiles different files " .
                            implode(', ', $files) . ", the Oktopus Autoloader will use the " .
                            "first file while autoloading the Object", E_USER_WARNING
                    );
                }
            }
        }
    }

    /**
     * Gets the list of known classes, with matching files (array of files path if multiple files for a given class name)
     *
     * @return array
     */
    public function getList()
    {
        $this->initialize();
        return $this->classes;
    }
}
