<?php

class Ot_View_Helper_FormLdap extends Zend_View_Helper_FormElement
{

    public function formLdap ($name, $value = null, $attribs = null)
    {
        $bindDn = '';
        $password = '';

        if (!is_array($value)) {
            $value = unserialize($value);
        }

        $bindDn = (isset($value['bindDn'])) ? $value['bindDn'] : '';
        $password = (isset($value['password'])) ? $value['password'] : '';      

        // return the 3 selects separated by &nbsp;
        return 
            $this->view->formLabel($name . '[bindDn]', 'Bind DN:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[bindDn]',
                $bindDn, array('class' => 'subelement')) . '<br />' .
                
            $this->view->formLabel($name . '[password]', 'Password:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[password]',
                $password, array('class' => 'subelement mask'))
        ;
    }

}