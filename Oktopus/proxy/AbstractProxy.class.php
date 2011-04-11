<?php
namespace Oktopus;

class ProxyException extends \Exception
{
}

class AbstractProxy
{
   protected $_proxied;

   public function __construct ($pProxied)
   {
      if (is_object($pProxied))
      {
         $this->_proxied = $pProxied;
      } else {
         throw new ProxyException('Proxys accept only objects as decoratees');
      }
   }

   public function __call ($pMethodName, $pArgs)
   {
      return call_user_func_array(array($this->_proxied, $pMethodName), $pArgs);
   }

   public function __get ($pPropertyName)
   {
      return $this->_proxied->$pPropertyName;
   }

   public function __set ($pPropertyName, $pValue)
   {
      $this->_proxied->$pPropertyName = $pValue;
   }

   public function __invoke ()
   {
      $args = func_get_args();
   	  $func = $this->_proxied;
      return call_user_func_array($func, $args);
   }

   public function __isset ($pPropertyName)
   {
      return isset($this->_proxied->$pPropertyName);
   } 
}