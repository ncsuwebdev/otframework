<?php
class Ot_Var_Type_Text extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Text($this->getName(), array('label' => $this->getLabel() . ':'));        
        $elm->setRequired($this->getRequired());
        
        if ($this->getDescription() != '') {
            $elm->setDescription($this->getDescription());
        }
        
        $elm->setValue($this->getValue());
        return $elm;
    }
}