<?php
namespace Oktopus;

class DecoratorGenerator
{
    private $_buffer;
    
    private $_indentation = 0;
    
    const DECORATOR   = 0;

    const SUBCLASSING = 1;

    public function generate ($pClassName, $pMode = self::SUBCLASSING)
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
        $this->_b('   private $_decorated;');
        
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

        $functionDeclaration = 'public function '.($method->returnsReference() ? '&' : '').$method->getName();
        $methodParameter = array();
        $methodCallParameter = array();
        foreach ($method->getParameters() as $parameter) {
        	//Type of the argument
        	if ($parameter->getClass() !== null) {
        		$parameterName = $parameter->getClass()->name.' $'.$parameter->getName(); 
        	} elseif ($parameter->isArray()) {
        		$parameterName = 'array $'.$parameter->getName();
        	} else {
        		$parameterName = '$'.$parameter->getName();
        	}
        	
        	//Default value of the argument
        	if ($parameter->isDefaultValueAvailable()) {
        		$parameterName .= ' = '.var_export($parameter->getDefaultValue(), true);
        	}

        	//Agument as it should be called to the delegated element
        	$methodParameter[] = $parameterName;
        	$methodCallParameter[] = '$'.$parameter->getName();
        }
        $methodParameter = implode(',', $methodParameter);
        $methodCallParameter = implode(',', $methodCallParameter);

        $this->_b($functionDeclaration.= '('.$methodParameter.')');
        $this->_b('{');
        $this->_b('   if (\Oktopus\AOP\Broker::instance()->beforeCall(\''.$reflection->getName().'\', \''.$method->getName().'\')) {');
        $this->_b('      \Oktopus\AOP\Broker::instance()->beforeCall(\''.$reflection->getName().'\', \''.$method->getName().'\');');
        $this->_b('   }');
        $this->_b('   try {');
        if ($pMode === self::DECORATOR) {
           $this->_b('      $return = $this->_decorated->'.$method->getName().'('.$methodCallParameter.');');
        } else {
           $this->_b('      $return = parent::'.$method->getName().'('.$methodCallParameter.');'); 
        }
        $this->_b('   } catch (\Exception $e) {');
        $this->_b('      return \Oktopus\AOP\Broker::instance()->catchException($e);');
        $this->_b('   }');
        $this->_b('   return \Oktopus\AOP\Broker::instance()->afterCall(\''.$reflection->getName().'\', \''.$method->getName().'\', $return);');
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