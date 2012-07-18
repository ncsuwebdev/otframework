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