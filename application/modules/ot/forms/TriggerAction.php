<?php
class Ot_Form_TriggerAction extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct(Zend_Form_SubForm $actionTypeSubForm, $options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'actionForm');
                  
        // Create and configure username element:
        $name = $this->createElement('text', 'name', array('label' => 'Action Nickname:'));
        $name->setRequired(true)
             ->addFilter('StringTrim')
             ->addFilter('StripTags');
        
        $this->addElements(array($name)); 
                
        $this->addSubForm($actionTypeSubForm, 'actionType');                

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'form-button-save'
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
