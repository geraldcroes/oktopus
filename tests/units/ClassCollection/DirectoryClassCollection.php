<?php
namespace Oktopus\ClassCollection\tests\units;

require_once __DIR__ . '/../../bootstrap.php';

use \mageekguy\atoum;

class DirectoryClassCollection extends atoum\test
{
    public function test__construct()
    {
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/nowarning/'), $parser);
    }

    public function testSilentGetSet()
    {
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/nowarning/'), $parser);

        $this->assert
        //Default values for silent mode are false
            ->boolean($classCollection->getSilentDuplicatesInDifferentFiles())
            ->isFalse()
            ->boolean($classCollection->getSilentDuplicatesInSameFile())
            ->isFalse()
        //set & get
            ->object($classCollection->setSilentDuplicatesInDifferentFiles(true))
            ->isIdenticalTo($classCollection)
            ->boolean($classCollection->getSilentDuplicatesInDifferentFiles())
            ->isTrue()
            ->object($classCollection->setSilentDuplicatesInDifferentFiles(false))
            ->isIdenticalTo($classCollection)
            ->boolean($classCollection->getSilentDuplicatesInDifferentFiles())
            ->isFalse()
            ->object($classCollection->setSilentDuplicatesInSameFile(true))
            ->isIdenticalTo($classCollection)
            ->boolean($classCollection->getSilentDuplicatesInSameFile())
            ->isTrue()
            ->object($classCollection->setSilentDuplicatesInSameFile(false))
            ->isIdenticalTo($classCollection)
            ->boolean($classCollection->getSilentDuplicatesInSameFile())
            ->isFalse();
    }

    public function testGetPathFirstCall()
    {
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/nowarning/'), $parser);

        $this->assert
            ->string($classCollection->getPath('foo'))
            ->isNotNull();
    }

    public function testGetKnownClassesFirstCall()
    {
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/nowarning/'), $parser);

        $this->assert
            ->array($classCollection->getKnownClasses())
            ->isNotEmpty()
            ->containsValues(array('foo', 'foo2', 'foo3', 'foofoo', 'foo\foo',
            'foo2\foo', 'foo2\foo2', 'foo3\foo'));
    }

    public function testWarningTwoSameClassesSameFile()
    {
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warning/'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $this->assert
            ->array($classCollection->getList())
            ->isNotEmpty();
        $this->assert
            ->error()
            ->exists()//first error
            ->exists();
        //second error

        //Silent mode
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warning/'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $classCollection->setSilentDuplicatesInSameFile(true);
        $this->assert
            ->array($classCollection->getList())
            ->isNotEmpty();
    }

    public function testAutoloaderWarningTwoSameClassesTwoFile()
    {
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warning2files'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $this->assert
            ->array($list = $classCollection->getList())
            ->hasKey('afoo');
        $this->assert
            ->error
            ->exists()
            ->exists()
            ->exists();

        //Silent mode (different files)
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warning2files'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $classCollection->setSilentDuplicatesInDifferentFiles(true);
        $this->assert
            ->array($classCollection->getList())
            ->hasKey('afoo');

        $this->assert
            ->error
            ->exists();
        //Just one error a AFoo is declared twice in the same file
        //other errors won't show up

        //Silent mode (same files)
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warning2files'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $classCollection->setSilentDuplicatesInSameFile(true);
        $this->assert
            ->array($list = $classCollection->getList())
            ->hasKey('afoo');

        $this->assert
            ->error
            ->exists()
            ->exists();
        //Two errors as the AFoo declared in the same file won't show up

        //Silent mode
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warning2files'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $classCollection->setSilentDuplicatesInSameFile(true)
            ->setSilentDuplicatesInDifferentFiles(true);
        $this->assert
            ->array($list = $classCollection->getList())
            ->hasKey('afoo');
    }

    public function testAutoloaderWarningTwoSameNamespaceClassesTwoFile()
    {
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warningnamespace2files'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $this->assert
            ->array($classCollection->getList())
            ->isNotEmpty();
        $this->assert
            ->error
            ->exists();

        //Silent won't raise warnings
        $classCollection = new \Oktopus\ClassCollection\DirectoryClassCollection($this->getDirectoryIterator(__DIR__ . '/../../resources/warningnamespace2files'), new \Oktopus\Parser\ClassParserForPHP5_3());
        $classCollection->setSilentDuplicatesInSameFile(true)
            ->setSilentDuplicatesInDifferentFiles(true);
        $this->assert
            ->array($classCollection->getList())
            ->isNotEmpty();
    }

    /**
     * @param $directoryPath
     * @return \RegexIterator
     */
    protected function getDirectoryIterator($directoryPath)
    {
        return new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directoryPath)
            ), '/^.*\.php$/i'
        );
    }
}