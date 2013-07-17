<?php
class Ot_Var_Type_Radio extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Radio($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());        
        $elm->setMultiOptions($this->getOptions());
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        $elm->setSeparator('');
        return $elm;
    }
    
    public function getDisplayValue() 
    {
        $options = $this->getOptions();
        
        return (isset($options[$this->getValue()])) ?  $options[$this->getValue()] : 'None';
    }
}