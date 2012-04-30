<?php
namespace Oktopus\ClassCollection\tests\units;

require __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../../Oktopus/ClassCollection/DirectoryIteratorAdaptatorForClassCollection.php';

use \mageekguy\atoum;

class DirectoryIteratorAdaptatorForClassCollection extends atoum\test
{
    public function test__construct ()
    {
        $directories = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(__DIR__.'/../../resources/nowarning/')
                ), '/^.*\.php$/i'
            );
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryIteratorAdaptatorForClassCollection($directories, $parser);
    }

    public function testSilentGetSet ()
    {
        $directories = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(__DIR__.'/../../resources/nowarning/')
                ), '/^.*\.php$/i'
            );
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryIteratorAdaptatorForClassCollection($directories, $parser);

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

    public function testGetPathFirstCall ()
    {
        $directories = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(__DIR__.'/../../resources/nowarning/')
                ), '/^.*\.php$/i'
            );
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryIteratorAdaptatorForClassCollection($directories, $parser);

        $this->assert
                ->string($classCollection->getPath('foo'))
                    ->isNotNull();
    }

    public function testGetKnownClassesFirstCall ()
    {
        $directories = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(__DIR__.'/../../resources/nowarning/')
                ), '/^.*\.php$/i'
            );
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $classCollection = new \Oktopus\ClassCollection\DirectoryIteratorAdaptatorForClassCollection($directories, $parser);

        $this->assert
                ->array($classCollection->getKnownClasses())
                    ->isNotEmpty()
                    ->containsValues(array('foo', 'foo2', 'foo3', 'foofoo', 'foo\foo',
                                           'foo2\foo', 'foo2\foo2', 'foo3\foo'));
    }
}