<?php
class Ot_Form_PasswordReset extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'resetPassword');

        $password = $this->createElement('password', 'password', array('label' => 'ot-login-passwordReset:new'));
        $password->setRequired(true)
                 ->addValidator('StringLength', false, array(6, 20))
                 ->addFilter('StringTrim')
                 ->addFilter('StripTags');

        $passwordConf = $this->createElement('password', 'passwordConf', array('label' => 'ot-login-passwordReset:confirm'));

        $passwordConf->setRequired(true)
                     ->addValidator('StringLength', false, array(6, 20))
                     ->addFilter('StringTrim')
                     ->addFilter('StripTags');

        $this->addElements(array($password, $passwordConf));

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'ot-login-passwordReset:linkReset'
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
