<?php
abstract class Ot_Var_Abstract
{
    protected $_name;
    protected $_label;
    protected $_description;
    protected $_defaultValue;
    protected $_options;
    protected $_required;
    protected $_value;    
    
    private $_cryptKey;

    public function __construct($name = '', $label = '', $description = '', $defaultValue = '', $options = array(), $required = false)
    {
        $this->setName($name);
        $this->setLabel($label);
        $this->setDescription($description);
        $this->setDefaultValue($defaultValue);
        $this->setOptions($options);
        $this->setRequired($required);
        
        $this->_cryptKey = 'config_' . $this->getName();
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

    public function setDefaultValue($defaultValue)
    {
        $this->_defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    public function setOptions($_options)
    {
        $this->_options = $_options;
        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setRequired($_required)
    {
        $this->_required = (bool) $_required;
        return $this;
    }

    public function getRequired()
    {
        return $this->_required;
    }
    
    abstract public function renderFormElement();

    public function setValue($value)
    {        
        $this->_value = $value;
    }

    public function getValue()
    {        
        return $this->_value;
    }
    
    public function getRawValue()
    {
        return $this->_value;
    }
    
    public function setRawValue($value)
    {
        $this->_value = $value;
    }
    
    public function getDisplayValue()
    {
        $value = $this->getValue();
        
        if (empty($value)) {
            $value = 'None';
        } elseif (is_array($value)) {
            $value = implode(', ', $value);
        }
        
        return $value;
    }
    
    public function __toString() {
        return ($this->getValue()) ? $this->getValue() : '';
    }
    
    protected function _encrypt($string)
    {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->_cryptKey), $string, MCRYPT_MODE_CBC, md5(md5($this->_cryptKey))));
    }
    
    protected function _decrypt($string)
    {
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->_cryptKey), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($this->_cryptKey))), "\0");
    }
}