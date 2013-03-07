<?php
class Ot_Form_AccountImport extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'noteForm')
             ->setDecorators(array(
                 'FormElements',
                 array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                 'Form',
             ));
                                               
        $text = $form->createElement('textarea', 'text', array('label' => ' Enter a comma separated list of Unity IDs:'));
        $text->addFilter('StringTrim')
             ->setAttrib('id', 'wysiwyg')
             ->setAttrib('style', 'width: 650px; height: 200px;')
             ->setValue((isset($default['text'])) ? $default['text'] : '');
        
        $roleList = array();
        $otRole = new Ot_Model_DbTable_Role();
        $allRoles = $otRole->fetchAll();
        foreach ($allRoles as $r) {
            $roleList[$r->roleId] = $r->name;
        }
             
        $newRoleId = $form->createElement('multicheckbox', 'newRoleId', array('label' => 'Choose new role for the accounts listed above: '));
        $newRoleId->setRequired(true);
        $newRoleId->setMultiOptions($roleList);
        $newRoleId->setValue((isset($default['newRoleId'])) ? $default['newRoleId'] : '');
              
        $form->addElements(array($text, $newRoleId));
        
        $submit = $form->createElement('submit', 'saveButton', array('label' => 'Submit'));
        $submit->setDecorators(array(
            array('ViewHelper', array('helper' => 'formSubmit'))
        ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
               array('ViewHelper', array('helper' => 'formButton'))
        ));        
                      
       
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
