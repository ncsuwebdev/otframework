<?php
class Ot_Form_Account extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($new = false, $me = false, $options = array())
    {
        parent::__construct($options);

        $acl = Zend_Registry::get('acl');

        $this->setAttrib('id', 'account');

        $authAdapter = new Ot_Model_DbTable_AuthAdapter;
        $adapters    = $authAdapter->fetchAll(null, 'displayOrder');

        // Realm Select box
        $realmSelect = $this->createElement('select', 'realm', array('label' => 'Login Method'));
        foreach ($adapters as $adapter) {
            $realmSelect->addMultiOption(
                $adapter->adapterKey,
                $adapter->name . (!$adapter->enabled ? ' (Disabled)' : '')
            );
        }

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

        // Email address field
        $email = $this->createElement('text', 'emailAddress', array('label' => 'model-account-emailAddress'));
        $email->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress');

        $timezone = $this->createElement('select', 'timezone', array('label' => 'model-account-timezone'));
        $timezone->addMultiOptions(Ot_Model_Timezone::getTimezoneList());
        $timezone->setValue(date_default_timezone_get());

        // Role select box
        $roleSelect = $this->createElement('multiselect', 'role', array('label' => 'model-account-role'));
        $roleSelect->setRequired(true);
        $roleSelect->setDescription('You may select multiple roles for a user');

        $roles = $acl->getAvailableRoles();
        foreach ($roles as $r) {
            $roleSelect->addMultiOption($r['roleId'], $r['name']);
        }

        if ($new) {
            $this->addElements(array($realmSelect, $username, $roleSelect, $firstName, $lastName, $email, $timezone));
        } else {

            if ($me) {
                $this->addElements(array($firstName, $lastName, $email, $timezone));
            } else {
                $realmSelect->setAttrib('disabled', 'disabled');
                $username->setAttrib('disabled', 'disabled');

                $this->addElements(array($realmSelect, $username, $roleSelect, $firstName, $lastName, $email, $timezone));
            }
        }

        $aar = new Ot_Account_Attribute_Register();

        $vars = $aar->getVars();

        foreach ($vars as $v) {
            $elm = $v->renderFormElement();
            $elm->clearDecorators();
            $elm->setBelongsTo('accountAttributes');

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
