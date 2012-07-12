<?php
class Ot_Var_Type_Date extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $options = $this->getOptions();
        
        $fields = array();
        
        /*
        if (isset($options['format'])) {
            $year = strpos($options['format'], 'y');
            $month = strpos($options['format'], 'm');
            $day = strpos($options['format'], 'd');
            $hour = strpos($options['format'], 'h');
            $minute = strpos($options['format'], 'i');
            $second = strpos($options['format'], 's');
            
            if ($year !== false) {
                $fields[$year] = new Zend_Form_Element_Text('config_' . $this->getName() . '_year');
            }
            
            if ($month !== false) {
                $fields[$month] = new Zend_Form_Element_Text('config_' . $this->getName() . '_month');
            }
            
            if ($day !== false) {
                $fields[$day] = new Zend_Form_Element_Text('config_' . $this->getName() . '_day');
            }
            
            if ($hour !== false) {
                $fields[$day] = new Zend_Form_Element_Text('config_' . $this->getName() . '_day');
            }
            
            if ($minute !== false) {
                $fields[$day] = new Zend_Form_Element_Text('config_' . $this->getName() . '_day');
            }
            
            if ($second !== false) {
                $fields[$day] = new Zend_Form_Element_Text('config_' . $this->getName() . '_day');
            }
            
            echo "<pre>";
            print_r($fields);
            die();
            
        }*/
        
        $elm = new Ot_Form_Element_Date('config_' . $this->getName(), array('label' => $this->getLabel() . ':', 'format' => $options['format']));
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        return $elm;
    }
}