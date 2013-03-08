<?php
class Ot_Form_ForgotPassword extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'forgotPassword');

        // Create and configure username element:
        $username = $this->createElement('text', 'username', array('label' => 'ot-login-form:username'));
        $username->setRequired(true)->addFilter('StringTrim');

        $this->addElements(array($username));

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'ot-login-forgot:linkReset'
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
