<?php
class Ot_Api_Endpoint
{
    protected $_name;
    protected $_description;
    protected $_method;

    public function __construct($name = '', $description = '', $module = '', $controller = '', $action = '')
    {
        $this->setName($name)
             ->setDescription($description);
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

    public function setMethod(Ot_Api_EndpointInterface $_method)
    {
        $this->_method = $_method;
        return $this;
    }

    public function getMethod()
    {
        return $this->_method;
    }
}