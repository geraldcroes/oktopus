<?php
namespace Oktopus\Parser\tests\units;

require_once __DIR__ . '/../../bootstrap.php';

use \mageekguy\atoum;

class ClassParserForPHP5_3 extends atoum\test
{
    public function testSimpleFile()
    {
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/foo.php'))
            ->isEqualTo(array('foo'));

        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/foo.php'))
            ->isEqualTo(array('foo'));
        //twice

        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/empty/empty.php'))
            ->isEmpty();

        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/empty/empty2.php'))
            ->isEmpty();

        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/foo2andfoo3.php'))
            ->isEqualTo(array('foo2', 'foo3'));
    }

    public function testNameSpace()
    {
        $parser = new \Oktopus\Parser\ClassParserForPHP5_3();
        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/namespaces/namespaces.php'))
            ->isEqualTo(array('foo\\foo', 'foofoo'));

        $this->assert
            ->phpArray($parser->find(__DIR__ . '/../../resources/nowarning/namespaces/namespaces2.php'))
            ->isEqualTo(array('foo2\\foo', 'foo2\\foo2', 'foo3\\foo'));
    }
}