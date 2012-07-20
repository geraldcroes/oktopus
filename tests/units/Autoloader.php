<?php
namespace Oktopus\tests\units;

require_once __DIR__ . '/../bootstrap.php';

use \mageekguy\atoum;


class Autoloader extends atoum\test
{
    /**
     * Test register / unregister.
     */
    public function testRegister()
    {
        $autoloader = new \Oktopus\Autoloader();
        $this->assert
            ->boolean($autoloader->isRegistered())
            ->isFalse();

        $autoloader->register();
        $this->assert
            ->boolean($autoloader->isRegistered())
            ->isTrue();

        $autoloader2 = new \Oktopus\Autoloader();
        $this->assert
            ->boolean($autoloader2->isRegistered())
            ->isFalse();

        $autoloader2->register();
        $this->assert
            ->boolean($autoloader2->isRegistered())
            ->isTrue();

        $autoloader2->unregister();
        $this->assert
            ->boolean($autoloader2->isRegistered())
            ->isFalse();
        $this->assert
            ->boolean($autoloader->isRegistered())
            ->isTrue();

        //Cannot register the same autoloader twice
        $this->assert
            ->exception(function () use($autoloader)
        {
            $autoloader->register();
        })
            ->IsInstanceOf('\Oktopus\AutoloaderException');
    }

    public function testAutoloaderRecursiveAndNonRecursive()
    {
        //testing recursive
        $autoloader = new \Oktopus\Autoloader ();
        $autoloader->addPath(__DIR__ . '/../resources/nowarning/', false);

        //we test that we can find foo, foo2 and foo3 (non recursive call)
        $this->assert->boolean($autoloader->autoload('foo'))->isTrue();
        $this->assert->boolean($autoloader->autoload('foo2'))->isTrue();
        $this->assert->boolean($autoloader->autoload('foo3'))->isTrue();

        //the class foo\foo is in a subdirectory, won't find it
        $this->assert->boolean($autoloader->autoload('foo\\foo'))->isFalse();

        //--- Recursive test
        $autoloader = new \Oktopus\Autoloader ();
        $autoloader->addPath(__DIR__ . '/../resources/nowarning/');

        //we test that we can find foo, foo2 and foo3 (non recursive call)
        $this->assert->boolean($autoloader->autoload('foo'))->isTrue();
        $this->assert->boolean($autoloader->autoload('foo2'))->isTrue();
        $this->assert->boolean($autoloader->autoload('foo3'))->isTrue();

        //the class foo\foo is in a subdirectory, have to find it
        $this->assert->boolean($autoloader->autoload('foo\\foo'))->isTrue();
    }
}