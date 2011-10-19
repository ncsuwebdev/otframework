<?php
class Ot_Var
{
    protected $_name;
    protected $_description;
    protected $_defaultValue;

    public function __construct($name = '', $description = '', $defaultValue = '')
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setDefaultValue($defaultValue);
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
}