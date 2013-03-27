<?php
class Ot_Var_Type_Ldap extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Ot_Form_Element_Ldap($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        return $elm;
    }       
    
    public function setValue($value)
    {        
        if (isset($value['password'])) {
            $value['password'] = $this->_encrypt($value['password']);
        }
                
        return parent::setValue(serialize($value));        
    }

    public function getValue()
    {
        $value = unserialize(parent::getValue());
                
        if (isset($value['password'])) {
            $value['password'] = $this->_decrypt($value['password']);
        }
                
        return $value;
    }       
}