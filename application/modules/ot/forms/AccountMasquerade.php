<?php
class Ot_Form_AccountImport extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $acl    = Zend_Registry::get('acl');

        $form = new Zend_Form();
        $form->setAttrib('id', 'account')->setDecorators(
            array('FormElements', array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')), 'Form')
        );

        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapters    = $authAdapter->fetchAll(null, 'displayOrder');

        // Realm Select box
        $realmSelect = $form->createElement('select', 'realm', array('label' => 'Login Method'));
        foreach ($adapters as $adapter) {
            $realmSelect->addMultiOption(
                $adapter->adapterKey,
                $adapter->name . (!$adapter->enabled ? ' (Disabled)' : '')
            );
        }
        $realmSelect->setValue((isset($default['realm'])) ? $default['realm'] : '');

        // Create and configure username element:
        $username = $form->createElement('text', 'username', array('label' => 'model-account-username'));
        $username->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('Alnum')
                 ->addFilter('StripTags')
                 ->addValidator('StringLength', false, array(3, 64))
                 ->setAttrib('maxlength', '64')
                 ->setValue((isset($default['username'])) ? $default['username'] : '');

        $submit = $form->createElement('submit', 'submit', array('label' => 'Masquerade'));
        $submit->setDecorators(
            array(
                array('ViewHelper', array('helper' => 'formSubmit'))
            )
        );

        $form->addElements(array($realmSelect, $username, $submit));

    }
}
