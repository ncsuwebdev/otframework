<?php
class Ot_Var_Type_Radio extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Radio($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());        
        $elm->setMultiOptions($this->getOptions());
        $elm->setValue($this->getValue());
        return $elm;
    }
}