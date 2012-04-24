<?php
class Ot_Var
{
    protected $_name;
    protected $_description;
    protected $_defaultValue;
    protected $_type;
    protected $_values;
    protected $_validTypes = array(
        'text',
        'textarea',
        'select',
        'password',
    );

    const ACL_ROLES = 'acl';

    public function __construct($name = '', $description = '', $defaultValue = '', $type = 'text', $values = array())
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setDefaultValue($defaultValue);
        $this->setType($type);
        $this->setValues($values);
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

    public function setDescription($_description)
    {
        $this->_description = $_description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->_defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    public function setType($_type)
    {
        $this->_type = $_type;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setValues($_values)
    {
        $this->_values = $_values;
        return $this;
    }

    public function getValues()
    {
        return $this->_values;
    }

}