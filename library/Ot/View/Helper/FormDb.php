<?php

class Ot_View_Helper_FormDb extends Zend_View_Helper_FormElement
{

    public function formDb ($name, $value = null, $attribs = null)
    {
        $host = '';
        $port = '';
        $username = '';
        $password = '';
        $dbname = '';

        if (!is_array($value)) {
            $value = unserialize($value);
        }

        $host = (isset($value['host'])) ? $value['host'] : '';
        $port = (isset($value['port'])) ? $value['port'] : '';
        $username = (isset($value['username'])) ? $value['username'] : '';
        $password = (isset($value['password'])) ? $value['password'] : '';
        $dbname = (isset($value['dbname'])) ? $value['dbname'] : '';            

        // return the 3 selects separated by &nbsp;
        return 
            $this->view->formLabel($name . '[host]', 'Host:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[host]',
                $host, array('class' => 'subelement')) . '<br />' .
                
            $this->view->formLabel($name . '[port]', 'Port:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[port]',
                $port, array('class' => 'subelement')) . '<br />' .
           
            $this->view->formLabel($name . '[username]', 'Username:', array('class' => 'sublabel')) .     
           
            $this->view->formText(
                $name . '[username]',
                $username, array('class' => 'subelement')) . '<br />' .
            
            $this->view->formLabel($name . '[password]', 'Password:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[password]',
                $password, array('class' => 'subelement mask')) . '<br />' . 
            
            $this->view->formLabel($name . '[dbname]', 'Database:', array('class' => 'sublabel')) . 
            
            $this->view->formText(
                $name . '[dbname]',
                $dbname, array('class' => 'subelement')
            );
    }

}