<?php
class Ot_Var_Type_Checkbox extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Checkbox($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        return $elm;
    }
    
    public function getDisplayValue()
    {
        return ($this->getValue()) ? 'Checked' : 'Not Checked';
    }
}