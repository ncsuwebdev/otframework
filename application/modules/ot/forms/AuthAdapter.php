<?php
class Ot_Form_AuthAdapter extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'authAdapterForm');

        $name = $this->createElement('text', 'name', array('label' => 'Display Name:'));
        $name->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '64');

        $description = $this->createElement('textarea', 'description', array('label' => 'Description:'));

        $description->setRequired(true)
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ->setAttrib('maxlength', '64');

        $this->addElements(array($name, $description));

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
