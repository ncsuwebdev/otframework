<?php
class Ot_Var_Type_Multicheckbox extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Ot_Form_Element_Multicheckbox($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions($this->getOptions());        
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        $elm->setSeparator('');
               
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