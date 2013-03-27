<?php
class Ot_CustomAttribute_FieldType
{
    protected $_key;
    protected $_name;
    protected $_varClassname;
    protected $_hasOptions;
    
    public function __construct($key = '', $name = '', $varClassname = '', $hasOptions = false)
    {
        $this->setKey($key);
        $this->setName($name);
        $this->setVarClassname($varClassname);        
        $this->setHasOptions($hasOptions);
    }

    public function setKey($_key)
    {
        $this->_key = $_key;
        return $this;
    }

    public function getKey()
    {
        return $this->_key;
    }
    
    public function setName($_name)
    {
        $this->_name = $_name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }
    
    public function setVarClassname($_varClassname)
    {
        $this->_varClassname = $_varClassname;
        return $this;
    }

    public function getVarClassname()
    {
        return $this->_varClassname;
    }
    
    public function setHasOptions($hasOptions)
    {
        $this->_hasOptions = (boolean) $hasOptions;
        return $this;
    }
    public function hasOptions()
    {
        return $this->_hasOptions;
    }
    
    public function getVar()
    {
        $reflection = new ReflectionClass($this->_varClassname);
        
        if (!$reflection->isSubclassOf('Ot_Var_Abstract')) {
            throw new Exception('Invalid var type found.  Must be of class Ot_Var_Abstract');
        }
        
        $var = new $this->_varClassname();
        
        return $var;
    }    
}