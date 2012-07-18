<?php

class Ot_Form_Element_Db extends Zend_Form_Element_Xhtml
{
    public $helper = 'formDb';

    public function isValid ($value, $context = null)
    {
        if (is_array($value)) {
            $value = serialize($value);
        }

        return parent::isValid($value, $context);
    }

    public function getValue()
    {
        if (is_array($this->_value)) {

            $value = serialize($this->_value);
            
            $this->setValue($value);
        }

        return parent::getValue();
    }
}