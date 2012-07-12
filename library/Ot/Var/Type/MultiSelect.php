<?php
class Ot_Var_Type_MultiSelect extends Ot_Var_Abstract
{
    public function renderFormElement()
    {        
        $elm = new Zend_Form_Element_MultiSelect('config_' . $this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setMultiOptions($this->getOptions());
        $elm->setValue($this->getValue());        
        $elm->setAttrib('style', 'width:200px;height:80px');
        return $elm;
    }
    
    public function setValue($value)
    {
        return parent::setValue(serialize($value));        
    }

    public function getValue()
    {
        $model = new Ot_Model_DbTable_Var();

        $thisVar = $model->find($this->getName());

        if (is_null($thisVar)) {
            $this->_value = $this->getDefaultValue();
        } else {
            $this->_value = unserialize($thisVar['value']);
        }
        
        return $this->_value;
    }
}