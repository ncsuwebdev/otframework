<?php
class Ot_Form_Role extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'roleForm');

        $name = $this->createElement('text', 'name', array('label' => 'model-role-form:roleName'));
        $name->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->addValidator('regex', true, array(
                  'pattern'   => '/^[a-z0-9\s\_\-]*$/i',
                  'messages'  =>  'Contains invalid characters for role name'))
              ->setAttrib('maxlength', '128')
              ->setDescription('Role names can only contain letters, numbers, spaces, dashes and underscores');

        $inheritRoleId = $this->createElement('select', 'inheritRoleId', array('label' => 'model-role-form:inheritRoleId'));

        $acl = Zend_Registry::get('acl');
        $roles = $acl->getAvailableRoles();

        $inheritRoleId->addMultiOption(0, 'No Inheritance');
        foreach ($roles as $r) {

            if (isset($values['roleId'])) {

                if (!$acl->inheritsRole($r['roleId'], $values['roleId']) && $r['roleId'] != $values['roleId']) {
                    $inheritRoleId->addMultiOption($r['roleId'], $r['name']);
                }
            } else {
                $inheritRoleId->addMultiOption($r['roleId'], $r['name']);
            }

        }

        $this->addElements(array($name, $inheritRoleId));

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'model-role-form:submit'
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
