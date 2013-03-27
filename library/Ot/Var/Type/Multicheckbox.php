<?php
class Ot_Var_Type_Multicheckbox extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Zend_Form_Element_Multicheckbox($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions($this->getOptions());        
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
               
        return $elm;
    }
    
    public function setValue($value)
    {
        return parent::setValue(serialize($value));        
    }

    public function getValue()
    {
        return unserialize(parent::getValue());
    }
}