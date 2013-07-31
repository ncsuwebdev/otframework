<?php
class Ot_Form_Config extends Twitter_Bootstrap_Form
{
    public function __construct($options = array())
    {
        parent::__construct($options);
                
        $register = new Ot_Config_Register();
        
        $vars = $register->getVars();
        
        $varsByModule = array();

        foreach ($vars as $v) {
            if (!isset($varsByModule[$v['namespace']])) {
                $varsByModule[$v['namespace']] = array();
            }

            $varsByModule[$v['namespace']][] = $v['object'];

        }  
        
        $section = new Zend_Form_Element_Select('section', array('label' => 'Select Configuration Section:'));
        $section->setDecorators(array(
                    'ViewHelper',
                    array(array('wrapperField' => 'HtmlTag'), array('tag' => 'div', 'class' => 'select-control')),                
                    array('Label', array('placement' => 'prepend', 'class' => 'select-label')),                   
                    array(array('wrapperAll' => 'HtmlTag'), array('tag' => 'div', 'class' => 'select-header')),                    
                ));
        $this->addElement($section);
        
        $sectionOptions = array();
        
        foreach ($varsByModule as $key => $value) {
            $group = array();
            foreach ($value as $v) {
                //$elm = $v->getFormElement();
                $elm = $v->renderFormElement();
                $elm->setDecorators(array(
                    'ViewHelper',
                    array('Errors', array('class' => 'help-inline')),
                    array(array('wrapperField' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fields')),                
                    array('Label', array('placement' => 'append', 'class' => 'field-label')),      
                    array('Description', array('placement' => 'append', 'tag' => 'div', 'class' => 'field-description')),                     
                    array(array('empty' => 'HtmlTag'), array('placement' => 'append', 'tag' => 'div', 'class' => 'clearfix')),
                    array(array('wrapperAll' => 'HtmlTag'), array( 'tag' => 'div', 'class' => 'field-group')),                    
                ));
                
                $group[] = $elm->getName();

                $this->addElement($elm);
            }

            $sectionOptions[preg_replace('/[^a-z]/i', '', $key)] = $key;
                        
            $this->addDisplayGroup($group, $key);
        }
        
        asort($sectionOptions);
        
        $section->setMultiOptions($sectionOptions);

        $this->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset'
        ));

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'Save Configuration'
        ));


        $this->addElement('button', 'cancel', array(
            'label'         => 'form-button-cancel',
            'type'          => 'button'
        ));

        $this->addDisplayGroup(
            array('submit', 'cancel'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions')
            )
        );

        return $this;

    }
}
