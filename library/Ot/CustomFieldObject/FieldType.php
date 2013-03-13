<?php
class Ot_CustomFieldObject_FieldType
{
    protected $_key;
    protected $_name;
    protected $_varClassname;
    
    public function __construct($key = '', $name = '', $varClassname = '')
    {
        $this->setKey($key);
        $this->setName($name);
        $this->setVarClassname($varClassname);        
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
    
    public function getVar()
    {
        $reflection = new ReflectionClass($this->_varClassname);
        
        if (!$reflection->isSubclassOf('Ot_Var_Abstract')) {
            throw new Exception('Invalid var type found.  Must be of class Ot_Var_Abstract');
        }
        
        return new $this->_varClassname();
    }
}