<?php
class Ot_Api_Endpoint
{
    protected $_name;
    protected $_description;
    protected $_methodClassname;

    public function __construct($name = '', $description = '', $methodClassname = '')
    {
        $this->setName($name)
             ->setDescription($description)
             ->setMethodClassname($methodClassname);
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

    public function setMethodClassname($_method)
    {
        $this->_methodClassname = $_method;
        return $this;
    }

    public function getMethodClassname()
    {
        return $this->_methodClassname;
    }
    
    public function getEndpointObj()
    {
        $reflection = new ReflectionClass($this->_methodClassname);
        
        if (!$reflection->isSubclassOf('Ot_Api_EndpointTemplate')) {
            throw new Exception('Invalid API endpoint type.  Must implement Ot_Api_EndpointTemplate');
        }
        
        return new $this->_methodClassname;
    }
}