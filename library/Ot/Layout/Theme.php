<?php
class Ot_Layout_Theme
{
    protected $_name;
    protected $_label;
    protected $_description;
    protected $_path;
    
    protected $_css = array(
        'prepend' => array(),
        'append'  => array(),
    );
    
    protected $_js = array(
        'prepend' => array(),
        'append'  => array(),
    );
    
    public function __construct($name = '', $label = '', $description = '', $path = '')
    {
        $this->setName($name);
        $this->setLabel($label);
        $this->setDescription($description);
        $this->setPath($path);
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

    public function setLabel($_label)
    {
        $this->_label = $_label;
        return $this;
    }

    public function getLabel()
    {
        return $this->_label;
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

    public function setPath($_path)
    {
        $this->_path = realpath($_path);
        return $this;
    }

    public function getPath()
    {
        return $this->_path;
    }
    
    public function getThemeUrlPath()
    {
        return str_replace(realpath(APPLICATION_PATH . '/../public/') . '/', '', $this->getPath());
    }
    
    public function addJs($scriptPath, $position = 'append')
    {
        if (isset($this->_js[$position])) {
            $this->_js[$position][] = $scriptPath;
        }        
    }
    
    public function getJs()
    {
        return $this->_js;
    }
    
    public function addCss($cssPath, $position = 'append')
    {
        if (isset($this->_css[$position])) {
            $this->_css[$position][] = $cssPath;
        }         
    }
    
    public function getCss()
    {
        return $this->_css;
    }
}