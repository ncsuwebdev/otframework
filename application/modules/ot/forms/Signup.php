<?php
class Ot_Form_Signup extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'signup');

        // Create and configure username element:
        $username = $this->createElement('text', 'username', array('label' => 'model-account-username'));
        $username->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('Alnum')
                 ->addFilter('StripTags')
                 ->addValidator('StringLength', false, array(3, 64))
                 ->setAttrib('maxlength', '64');

        // First Name
        $firstName = $this->createElement('text', 'firstName', array('label' => 'model-account-firstName'));
        $firstName->setRequired(true)
                  ->addFilter('StringToLower')
                  ->addFilter('StringTrim')
                  ->addFilter('StripTags')
                  ->addFilter(new Ot_Filter_Ucwords())
                  ->setAttrib('maxlength', '64');

        // Last Name
        $lastName = $this->createElement('text', 'lastName', array('label' => 'model-account-lastName'));
        $lastName->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('StringToLower')
                 ->addFilter('StripTags')
                  ->addFilter(new Ot_Filter_Ucwords())
                 ->setAttrib('maxlength', '64');

        // Password field
        $password = $this->createElement('password', 'password', array('label' => 'model-account-password'));
        $password->setRequired(true)
                 ->addValidator('StringLength', false, array($this->_minPasswordLength, $this->_maxPasswordLength))
                 ->addFilter('StringTrim')
                 ->addFilter('StripTags');

        // Password confirmation field
        $passwordConf = $this->createElement('password', 'passwordConf', array('label' => 'model-account-passwordConf'));
        $passwordConf->setRequired(true)
                     ->addValidator('StringLength', false, array($this->_minPasswordLength, $this->_maxPasswordLength))
                     ->addValidator('Identical', false, array('token' => 'password'))
                     ->addFilter('StringTrim')
                     ->addFilter('StripTags');

        // Email address field
        $email = $this->createElement('text', 'emailAddress', array('label' => 'model-account-emailAddress'));
        $email->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress');

        $timezone = $this->createElement('select', 'timezone', array('label' => 'model-account-timezone'));
        $timezone->addMultiOptions(Ot_Model_Timezone::getTimezoneList());
        $timezone->setValue(date_default_timezone_get());

        $this->addElements(array($username, $password, $passwordConf, $firstName, $lastName, $email, $timezone));

        $aar = new Ot_Account_Attribute_Register();

        $vars = $aar->getVars();

        foreach ($vars as $v) {
            $elm = $v->renderFormElement();
            $elm->clearDecorators();
            $elm->setBelongsTo('attributes');

            $this->addElement($elm);
        }

        $cahr = new Ot_CustomAttribute_HostRegister();

        $thisHost = $cahr->getHost('Ot_Profile');

        if (is_null($thisHost)) {
            throw new Ot_Exception_Data('msg-error-objectNotSetup');
        }
        
        $customAttributes = $thisHost->getAttributes();
        
        foreach ($customAttributes as $a) {
            $elm = $a['var']->renderFormElement();
            $elm->clearDecorators();
            $elm->setBelongsTo('customAttributes');
            
            $this->addElement($elm);
        }

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'Create My Account'
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
