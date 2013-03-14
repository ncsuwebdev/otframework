<?php
class Ot_Form_CustomAttributeOption extends Zend_Form_Subform
{
    public function init()
    {
        $this->setAttrib('id', 'customAttribute');
            
        $this->addElement('text', 'option');
        
        $this->addElement('button', 'removeElement', array(
            'label'        => '<i class="icon-white icon-ban-circle"></i>',
            'class'        => 'btn btn-danger removeButton',
            'escape'       => false,
        )); 
        
        $this->addDisplayGroup(
            array('option', 'removeElement'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array(
                    array('FormElements'),
                    array(array('inputWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'input-append')),
                    array(array('wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'controls'))
                )
            )
        );
        
        $this->setDecorators(array('FormElements'));
        $this->setElementDecorators(array('ViewHelper', 'FormElements'));
        
        return $this;

    }    
}
