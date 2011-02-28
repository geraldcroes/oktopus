<?php
namespace Oktopus;

class Definition {
    protected $_definitions = array();
    protected $_name;
     
    public function __construct ($pName, Definition $pParent) {
        $this->_name = $pName;
    }

    public function add (Definition $pDefinition){
        $this->_definitions[] = $pDefinition;
    }
    
    public function isA ($pName, $pType) {
        return strtolower(get_class($this)) === strtolower($pType)
            && $pName === $this->getName(); 
    }

    public function hasFunction ($pName) {
        return $this->has($pName, 'FileDefinition');
    }
    
    public function hasMethod ($pName) {
        return $this->has($pName, 'MethodDefinition');
    }
    
    public function hasClass ($pName) {
        return $this->has($pName, 'ClassDefinition');
    }
    
    public function hasNamespace ($pName) {
        return $this->has($pName, 'NamespaceDefinition');
    }
    
    public function hasFile ($pName) {
        return $this->has($pName, 'FileDefinition');
    }
    
    protected function has ($pName, $pType) {
        //is there the given seached element ?
        foreach ($this->_definitions as $definition){
            if ($definition->isA($pName, $pType)){
                return true;
            } elseif ($definition->has($pName, $pType)) {
                return true;
            }
        }
        return false;
    }

    public function getName () {
        return $this->_name;
    }
}

class FileDefinition extends Definition {

}

class FunctionDefinition extends Definition {

}

class ClassDefinition extends Definition {
}

class InterfaceDefinition extends Definition {
}

class MethodDefinition extends Definition {
}

class NamespaceDefinition extends Definition {
}

class Braces {
}




class CodeParser {
    protected $_current;
    
    protected $_tokens;
    
    public function analyse ($pFileName) {
        if (!is_readable($pFileName)) {
            throw new Exception('Cannot read $pFileName');
        }

        $this->_tokens = new \ArrayIterator(token_get_all($fileContent = file_get_contents($pFileName)));
        $this->_log($fileContent);
        
        while ($token = $this->_tokens->current()){
            if (!is_array($token)){
                if ($token === '}'){
                } elseif ($token === '{') {
                }
                $this->_tokens->next();
            } else {
                switch ($token[0]) {
                    case T_NAMESPACE:
                        $this->_readNamespace(T_NAMESPACE);
                    default: 
                        $this->_tokens->next();
                }
            }
        }
    }
    
    protected function _readNamespace ($pType){
        $this->_log('Looking for a namespace');
        $name = '';
        do {
            if ($this->_tokens->next() === false){
                break;
            }
            
            $token = $this->_tokens->current();
            if (is_array($token)){
                if ($token[0] === T_WHITESPACE){
                    
                } elseif ($token[0] === T_STRING){
                    $this->_log("Adding $token[1] into namespace $name");
                    $name.=$token[1];
                } else {
                    $this->_log("Terminated namespace hunt with ".token_name($token[0]));
                }
            } else {
                if ($token === ';' || $token === '{'){
                    $this->_log("Namespace is $name ended with $token");
                    return $name;
                } else {
                    $this->_log("founded a $token");
                }
            }            
        }while ($this->_tokens->current());        
    }
    
    private function _log($pMessage, $pLevel = 0){
        if (is_array($pMessage)){
            foreach($pMessage as $message){
                $this->_log($message, $pLevel++);
            }
        } else {
            echo str_repeat(' ', $pLevel), $pMessage, "\n";
        }
    }
}