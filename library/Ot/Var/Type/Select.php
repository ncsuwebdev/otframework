<?php
class Ot_Var_Type_Select extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Select('config_' . $this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions($this->getOptions());
        $elm->setValue($this->getValue());
        return $elm;
    }
}