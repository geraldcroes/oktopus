<?php
namespace Oktopus;

class Proxy
{
   public function __call ($pName, $pArgs)
   {
      $this->_beforeMethodCall($pName, $pArgs);
      try {
         $toReturn = parent::_call($pName, $pArgs);
      } catch (\Exception $e) {
         $this->_afterMethodCall($pName, $pArgs, $e);
         throw $e;
      }
      $this->_afterMethodCall($pName);
      return $toReturn;
   }

   public function __set ($pName, $pValue)
   {
      $this->_beforePropertySet($pName, $pValue);
      try {
         parent::__set($pName, $pValue);
      } catch (\Exception $e) {
         $this->_afterPropertySet($pName, $pValue, $e);
         throw $e;
      }
      $this->_afterPropertySet($pName, $pValue);
   }

   public function __get ($pName)
   {
      $this->_beforePropertyGet($pName);
      try {
         $toReturn = parent::__get($pName);
      } catch (\Exception $e) {
         $this->_afterPropertyGet($pName);
         throw $e;
      }
      return $toReturn;
   }
   
   public function __invoke ($pArgs)
   {
      $this->_beforeMethodCall ('__invoke', $pArgs);
      try {
         $toReturn = parent::__invoke($pArgs);
      } catch (\Exception $e) {
         $this->_afterMethodCall('__invoke', $pArgs);
         throw $e;
      }
      return $toReturn;
   }
}
