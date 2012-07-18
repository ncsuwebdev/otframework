<?php
class Ot_Var_Type_Db extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Ot_Form_Element_Db('config_' . $this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        return $elm;
    }   
}