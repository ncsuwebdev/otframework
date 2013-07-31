<?php
class Ot_Form_UserSearch extends Zend_Form
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'userSearchForm')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'well')),
                     'Form',
             ))
             ->setMethod(Zend_Form::METHOD_GET);

        $username = $this->createElement('text', 'username', array('label' => 'Username:'));

        $otRole = new Ot_Model_DbTable_Role();        
        $allRoles = $otRole->fetchAll();        
        
        $role = $this->createElement('select', 'role', array('label' => 'Role:'));
        $role->addMultiOption('', 'Any Role');
        
        foreach ($allRoles as $r) {
            $role->addMultiOption($r->roleId, $r->name);
        }
        
        $firstName = $this->createElement('text', 'firstName', array('label' => 'First Name:'));
        
        $lastName = $this->createElement('text', 'lastName', array('label' => 'Last Name:'));

        $this->addElements(array($username, $firstName, $lastName, $role));
            
        $this->setElementDecorators(array(
                  'ViewHelper',
                  array(array('wrapperField' => 'HtmlTag'), array('tag' => 'div', 'class' => 'elm')),
                  array('Errors', array('placement' => 'append')),
                  array('Label', array('placement' => 'prepend')),
                  array(array('wrapperAll' => 'HtmlTag'), array('tag' => 'div', 'class' => 'criteria')),
              ));

        $sort = $this->createElement('hidden', 'sort');
        $sort->setDecorators(array('ViewHelper'));

        $direction = $this->createElement('hidden', 'direction');
        $direction->setDecorators(array('ViewHelper'));
        
        $submit = $this->createElement('submit', 'submitButton', array('label' => 'Filter Results'));
        $submit->setAttrib('class', 'btn btn-danger');
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit')),
                   array(array('wrapperAll' => 'HtmlTag'), array('tag' => 'div', 'class' => 'submit')),
                   array('HtmlTag', array('tag' => 'div', 'class' => 'clearfix')),
                 ));
        
        $this->addElements(array($submit, $sort, $direction));

        return $this;

    }
}
