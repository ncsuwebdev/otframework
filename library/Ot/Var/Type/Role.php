<?php
class Ot_Var_Type_Role extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Select($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());
        $elm->setRequired($this->getRequired());

        $acl = Zend_Registry::get('acl');
        $roles = $acl->getAvailableRoles();

        foreach ($roles as $r) {
            $elm->addMultiOption($r['roleId'], $r['name']);
        }

        $elm->setValue($this->getValue());
        return $elm;
    }
}