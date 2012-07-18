<?php
class Ot_Var_Type_Password extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Text('config_' . $this->getName(), array('label' => $this->getLabel() . ':', 'class' => 'mask'));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        return $elm;
    }
}