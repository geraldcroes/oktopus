<?php
namespace Oktopus;

class DecoratorGenerator
{
   public function generate ($pClassName)
   {
      $reflection = new \ReflectionClass($pClassName);
      $arrayData = array();
      $arrayData['classname'] = $reflection->getName();
      $arrayData['namespace'] = $reflection->getNamespace();
      $arrayData['interface'] = $reflection->getInterfaceNames();
      //We're gonna check methods that accepts parameters as references
      foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
         $parameters = array();
         if ($method->returnsReference()) {
            $accept = true;
         }
         foreach ($method->getParameters() as $parameter) {
            $parameterDefinition = array();
            $parameterDefinition['reference'] = $parameter->isPassedByReference();
            if ($parameterDefinition['reference']) {
               $accept = true;
            }
            
         } 
      }
      return $this->_fetch($arrayData);
   }

   private function _fetch ($arrayData)
   {
      $buffer = '<?php';
      $buffer .= "class Decorator{$arrayData['classname']}";
      $buffer .= "{";
      $buffer .= '   private $_decorated';
      $buffer .= "}";
      return $buffer;
      
   }
}
