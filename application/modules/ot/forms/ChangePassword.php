<?php
class Ot_Form_ChangePassword extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);
        
        $this->setAttrib('id', 'changePassword');

        $oldPassword = $this->createElement(
            'password',
            'oldPassword',
            array('label' => 'ot-account-changePassword:form:oldPassword')
        );
        $oldPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(5, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags');

        $newPassword = $this->createElement(
            'password',
            'newPassword',
            array('label' => 'ot-account-changePassword:form:newPassword')
        );
        $newPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(5, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags');

        $newPasswordConf = $this->createElement(
            'password',
            'newPasswordConf',
            array('label' => 'ot-account-changePassword:form:newPasswordConf')
        );
        $newPasswordConf->setRequired(true)
                        ->addValidator('StringLength', false, array(5, 20))
                        ->addFilter('StringTrim')
                        ->addFilter('StripTags');


        $this->addElements(array($oldPassword, $newPassword, $newPasswordConf));
       
        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'ot-account-changePassword:form:submit'
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
