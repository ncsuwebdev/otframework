<?php
class Ot_Var_Type_Password extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Text($this->getName(), array('label' => $this->getLabel() . ':', 'class' => 'mask'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        return $elm;
    }
    
    public function setValue($value)
    {           
        return parent::setValue($this->_encrypt($value));
    }

    public function getValue()
    {
        return $this->_decrypt(parent::getValue());
    }   
    
    public function getDisplayValue() 
    {
        return 'Protected';
    }  
}