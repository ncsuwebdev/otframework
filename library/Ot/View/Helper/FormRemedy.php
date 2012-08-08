<?php

class Ot_View_Helper_FormRemedy extends Zend_View_Helper_FormElement
{

    public function formRemedy ($name, $value = null, $attribs = null)
    {
        $username = '';
        $password = '';

        if (!is_array($value)) {
            $value = unserialize($value);
        }

        $username = (isset($value['username'])) ? $value['username'] : '';
        $password = (isset($value['password'])) ? $value['password'] : '';      

        // return the 3 selects separated by &nbsp;
        return 
            $this->view->formLabel($name . '[username]', 'Username:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[username]',
                $username, array('class' => 'subelement')) . '<br />' .
                
            $this->view->formLabel($name . '[password]', 'Password:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[password]',
                $password, array('class' => 'subelement mask'))
        ;
    }

}