<?php
class Ot_Var_Type_Textarea extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Textarea($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        return $elm;
    }
    
    public function getDisplayValue() {
        return nl2br(parent::getDisplayValue());
    }
}