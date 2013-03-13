<?php
class Ot_Form_TriggerAction extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($actionTypeSubForm, $options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'actionForm');
                  
        // Create and configure username element:
        $name = $this->createElement('text', 'name', array('label' => 'Action Nickname:'));
        $name->setRequired(true)
             ->addFilter('StringTrim')
             ->addFilter('StripTags')
             ->setDescription('A simple identifier to remind you the purpose of this action.');
        
        $actionTypeSubForm->setDecorators(array('FormElements'));
        
        $actionTypeSubForm->setElementDecorators(array(
            array('FieldSize'),
            array('ViewHelper'),
            array('Addon'),
            array('ElementErrors'),
            array('Description', array('tag' => 'p', 'class' => 'help-block')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'controls')),
            array('Label', array('class' => 'control-label')),
            array('Wrapper')
        ));
                
        $this->addElements(array($name)); 
                
        $this->addSubForm($actionTypeSubForm, 'actionType');

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'Save Action'
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
