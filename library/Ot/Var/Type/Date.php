<?php
class Ot_Var_Type_Date extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $options = $this->getOptions();

        $elm = new Ot_Form_Element_Date($this->getName(), array('label' => $this->getLabel() . ':', 'format' => $options['format']));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        return $elm;
    }
}