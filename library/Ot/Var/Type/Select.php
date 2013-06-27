<?php
class Ot_Var_Type_Select extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Select($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions(array_merge(array('' => '---- Select One ----'), $this->getOptions()));
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        return $elm;
    }
    
    public function getDisplayValue() 
    {
        $options = $this->getOptions();
        
        return (isset($options[$this->getValue()])) ?  $options[$this->getValue()] : 'None';
    }
}