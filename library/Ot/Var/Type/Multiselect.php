<?php
class Ot_Var_Type_Multiselect extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Zend_Form_Element_Multiselect($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions($this->getOptions());        
        $elm->setValue($this->getValue());        
        $elm->setRequired($this->getRequired());
        $elm->setAttrib('style', 'width:300px;height:100px');
               
        return $elm;
    }
    
    public function setValue($value)
    {
        return parent::setValue(serialize($value));        
    }

    public function getValue()
    {
        return unserialize(parent::getValue());
    }
    
    public function getDisplayValue()
    {
        $options = $this->getOptions();
        
        $values = $this->getValue();
        
        $outputValues = array();
        foreach ($values as $v) {
            if (isset($options[$v])) {
                $outputValues[] = $options[$v];
            }
        }
        
        return implode(', ', $outputValues);
    }
}