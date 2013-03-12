<?php
abstract class Ot_Trigger_ActionType_Abstract
{
    protected $_name;
    protected $_key;
    protected $_description;
    
    public function __construct($key = '', $name = '', $description = '')
    {
        $this->setKey($key);
        $this->setName($name);
        $this->setDescription($description);
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
    
    /**
     * Subform to add a new trigger
     *
     * @return Zend_Form element
     */
    abstract public function addSubForm();
    
    /**
     * Action called when the addForm is processed
     *
     * @param array $data
     */
    abstract public function addProcess($data);
    
    /**
     * Subform to edit an existing trigger
     *
     * @param mixed $id
     * @return Zend_Form element
     */
    abstract public function editSubForm($id);
    
    /**
     * Action called when the editForm is processed
     *
     * @param array $data
     */
    abstract public function editProcess($data);
    
    /**
     * Action called when a request is processed to delete a trigger
     *
     * @param mixed $id
     * @return boolean
     */
    abstract public function deleteProcess($id);
    
    /**
     * retrieves trigger with a specific ID
     *
     * @param mixed $id
     * @return Zend_Db_Table_Rowset or null
     */
    abstract public function get($id);
    
    /**
     * Action called when a trigger is executed.
     *
     * @param array $data
     */
    abstract public function dispatch($data);
}