<?php
class Ot_Account_Profile_Page
{
    protected $_id;
    protected $_label;
    protected $_module;
    protected $_controller;
    protected $_action;
    protected $_vars = array();
    
    public function __construct($id = '', $label = '', $module = '', $controller = '', $action = '', array $vars = array())
    {
        $this->setId($id)
                ->setLabel($label)
                ->setModule($module)
                ->setController($controller)
                ->setAction($action)
                ->setVars($vars);
        
        return $this;
        
    }
    
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function setLabel($_label)
    {
        $this->_label = $_label;
        return $this;
    }

    public function getLabel()
    {
        return $this->_label;
    }
    
    public function setModule($_module)
    {
        $this->_module = $_module;
        return $this;
    }

    public function getModule()
    {
        return $this->_module;
    }
    
    public function setController($_controller)
    {
        $this->_controller = $_controller;
        return $this;
    }

    public function getController()
    {
        return $this->_controller;
    }
    
    public function setAction($_action)
    {
        $this->_action = $_action;
        return $this;
    }

    public function getAction()
    {
        return $this->_action;
    }
    
    public function setVars(array $_vars)
    {
        $this->_vars = $_vars;
        return $this;
    }

    public function getVars()
    {
        return $this->_vars;
    }
}
