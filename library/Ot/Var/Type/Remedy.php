<?php
class Ot_Var_Type_Remedy extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Ot_Form_Element_Remedy($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        return $elm;
    }       
    
    public function setValue($value)
    {        
        $value['password'] = $this->_encrypt($value['password']);
                
        return parent::setValue(serialize($value));        
    }

    public function getValue()
    {
        $value = unserialize(parent::getValue());
                
        $value['password'] = $this->_decrypt($value['password']);
                
        return $value;
    }         
}