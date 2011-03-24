<?php
namespace Oktopus;

class DecoratorGenerator
{
    private $_buffer;
    
    private $_indentation = 0;
    
    const DECORATOR   = 0;

    const SUBCLASSING = 1;

    public function generate ($pClassName, $pMode = self::DECORATOR)
    {
        $this->_buffer = '';

        $reflection = new \ReflectionClass($pClassName);
        return $this->_fetch($reflection, $pMode);
    }

    private function _fetch (\ReflectionClass $reflection, $pMode)
    {
        $this->_b('<?php');

        $this->_b($reflection->getDocComment());
        $classDeclaration = "class Decorator".$reflection->getName();
        if ($pMode === self::DECORATOR) {
            if (count ($interfaces = $reflection->getInterfaceNames()) > 0) {
                $classDeclaration .= ' implements '.implode(', ', $interfaces);
            }
        } else {
            $classDeclaration .= ' extends '.$reflection->getName();
        }

        $this->_b($classDeclaration);
        $this->_b("{");
        $this->_b('   private $_decorated');
        
        $this->_bia(3);
        foreach ($reflection->getMethods() as $method) {
            $this->_generateMethod($method, $reflection, $pMode);
        }
        $this->_bir(3);
        $this->_b ("}");
        return $this->_buffer;
    }
     
    private function _generateMethod (\ReflectionMethod $method, \ReflectionClass $reflection, $pMode)
    {
        $this->_b ($method->getDocComment());

        $functionDeclaration = 'public function '.$method->getName();
        $methodParameter = '';
        $methodCallParameter = '';
        foreach ($method->getParameters() as $parameter) {
            echo " name : ", $parameter->getName();
            echo " isPassedByReference : ", $parameter->isPassedByreference();
            echo " getDeclaringFunction : ", $parameter->getDeclaringFunction();
            echo " getDeclaringClass : ", $parameter->getDeclaringClass();
            echo " getClass : ", $parameter->getClass();
            echo " isArray : ", $parameter->isArray();
            echo " allowsNull : ", $parameter->allowsNull();
            echo " getPosition : ", $parameter->getPosition();
        }
        $this->_b($functionDeclaration);
        $this->_b('{');
        $this->_b('   \Oktopus\AOP\Brocker::instance()->beforeCall('.$reflection->getName().', '.$method->getName().')');
        $methodCall = 'foo';
        $this->_b('   try {');
        if ($pMode === self::DECORATOR) {
           $this->_b('      $return = $this->_decorator->'.$methodCall);
        } else {
           $this->_b('      $return = parent::'.$methodCall); 
        }
        $this->_b('   } catch (\Exception $e) {');
        $this->_b('      ');
        $this->_b('   }');
        $this->_b('   \Oktopus\AOP\Brocker::instance()->afterCall('.$reflection->getName().', '.$method->getName().', $return)');
        $this->_b('}');
    }

    private function _b ($pText)
    {
        $this->_buffer .= str_repeat(' ', $this->_indentation).$pText."\n\r";
    }
    
    private function _bia ($pNum)
    {
        $this->_indentation += $pNum;
    }
    
    private function _bir ($pNum)
    {
        $this->_indentation -= $pNum;
    }
}