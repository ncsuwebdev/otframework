<?php
class Ot_Var_Type_Multiselect extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Zend_Form_Element_Multiselect($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions($this->getOptions());        
        $elm->setValue($this->getValue());        
        $elm->setAttrib('style', 'width:200px;height:80px');
               
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