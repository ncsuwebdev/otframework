<?php
abstract class Ot_Trigger_ActionType_Abstract
{
    protected $_name;
    protected $_key;
    protected $_description;
    protected $_dbtable;
    protected $_form;
    
    public function __construct($key = '', $name = '', $description = '')
    {
        $this->setKey($key);
        $this->setName($name);
        $this->setDescription($description);
        
        if (!$this->_dbtable) {
            throw new Ot_Exception('Action Type does not have a DB table set');
        }
        
        if (!$this->_form) {
            throw new Ot_Exception('Action type does not have a form set');
        }
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

    public function setKey($_key)
    {
        $this->_key = $_key;
        return $this;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function setDescription($_description)
    {
        $this->_description = $_description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }
    
    public function getForm()
    {
        $class = new ReflectionClass($this->_form);
        
        if (!$class->isSubclassOf('Zend_Form_Subform')) {
            throw new Exception('Form must be a valid Zend_Form_Subform object');
        }
        
        return new $this->_form();
    }
            
    public function getDbTable()
    {
        $class = new ReflectionClass($this->_dbtable);
        
        if (!$class->isSubclassOf('Ot_Db_Table')) {
            throw new Exception('DB Table must be a valid Ot_Db_Table object');
        }
        
        return new $this->_dbtable();
    }    
    
    /**
     * Action called when a trigger is executed.
     *
     * @param array $data
     */
    abstract public function dispatch(array $data);
}