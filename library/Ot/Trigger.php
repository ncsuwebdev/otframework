<?php
class Ot_Trigger
{
    protected $_name;
    protected $_key;
    protected $_description;
    protected $_options;

    public function __construct($name, $key, $description = '')
    {
        $this->setName($name);
        $this->setKey($key);
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

    public function addOption($key, $description)
    {
        $this->_options[$key] = $description;
        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }
}