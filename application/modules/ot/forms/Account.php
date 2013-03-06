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

        // Role select box
        $roleSelect = $this->createElement('multiSelect', 'roleSelect', array('label' => 'model-account-role'));
        $roleSelect->setRequired(true);
        $roleSelect->setDescription('You may select multiple roles for a user');

        $roles = $acl->getAvailableRoles();
        foreach ($roles as $r) {
            $roleSelect->addMultiOption($r['roleId'], $r['name']);
        }
        
        if ($new) {
            $this->addElements(array($realmSelect, $username, $password, $passwordConf, $roleSelect, $firstName, $lastName, $email, $timezone));
        } else {
            
            if ($me) {
                $this->addElements(array($firstName, $lastName, $email, $timezone));
            } else {
                $this->addElements(array($roleSelect, $firstName, $lastName, $email, $timezone));
            }
        }        
                
        $aar = new Ot_Account_Attribute_Register();
        
        $vars = $aar->getVars();
        
        foreach ($vars as $v) {
            $elm = $v->renderFormElement();
            $elm->clearDecorators();
            $elm->setBelongsTo('accountattribute');
            
            $this->addElement($elm);
        }
        
        /*
        
        $subformElements = array();

        
        $loginOptions = Zend_Registry::get('applicationLoginOptions');

        if (isset($loginOptions['accountPlugin'])) {
            $acctPlugin = new $loginOptions['accountPlugin'];

            if (isset($default['accountId'])) {
                $subform = $acctPlugin->editSubForm($default['accountId']);
            } else {
                $subform = $acctPlugin->addSubForm();
            }

            foreach ($subform->getElements() as $e) {
                $this->addElement($e);
                $subformElements[] = $e->getName();
            }
        }
         
         */

        $custom = new Ot_Model_Custom();

        if (isset($default['accountId'])) {
            $attributes = $custom->getData('Ot_Profile', $default['accountId'], 'Zend_Form');
        } else {
            $attributes = $custom->getAttributesForObject('Ot_Profile', 'Zend_Form');
        }

        $attributeNames = array();
        foreach ($attributes as $a) {
            $a['formRender']->clearDecorators();
            
            $this->addElement($a['formRender']);
            if(isset($a['attribute'])) {
                $attributeNames[] = 'custom_' . $a['attribute']['attributeId'];
            } else {
                $attributeNames[] = 'custom_' . $a['attributeId'];
            }
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
