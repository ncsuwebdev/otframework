<?php
class Ot_Var_Type_Description extends Ot_Var_Abstract
{   
    public function renderFormElement()
    {
        $elm = new Ot_Form_Element_Description($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setValue(true);
        
        return $elm;
    }
}