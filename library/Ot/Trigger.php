<?php
class Ot_Trigger
{
    protected $_name;
    protected $_description;
    protected $_options;

    public function __construct($name = '', $description = '')
    {
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