<?php
namespace Oktopus;

class DecoratorException extends \Exception
{
}

class AbstractDecorator
{
   protected $_decorated;

   public function __construct ($pDecorated)
   {
      if (is_object($pDecorated))
      {
         $this->_decorated = $pDecorated;
      } else {
         throw new DecoratorException('Decorators accept only objects as decoratees');
      }
   }

   public function __call ($pMethodName, $pArgs)
   {
      return call_func_array(array($this->_decorated, $pMethodName), $pArgs);
   }

   public function __get ($pPropertyName)
   {
      return $this->$pPropertyName;
   }

   public function __set ($pPropertyName, $pValue)
   {
      $this->$pPropertyName = $pValue;
   }

   public function __invoke ()
   {
      $func = $this->_decorated;
      return $func();
   }

   public function __isset ($pPropertyName)
   {
      return isset($this->_decorated->$pPropertyName);
   } 
}


