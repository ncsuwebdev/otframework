<?php

class Ot_Form_Element_Multicheckbox extends Zend_Form_Element_MultiCheckbox
{
    public function setValue($value) 
    {
        if ($value === false) {
            $value = null;
        }
        
        parent::setValue($value);
    }
}
