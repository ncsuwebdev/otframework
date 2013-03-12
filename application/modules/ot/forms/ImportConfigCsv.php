<?php
class Ot_Form_ImportConfigCsv extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'importConfigCsvForm')
             ->setAttrib('enctype', 'multipart/form-data');

        $file = $this->createElement('file', 'config', array('label' => 'Upload a CSV File:'));
        $file->addValidator('Count', false, 1)
             ->addValidator('Size', false, 102400)
             ->addValidator('Extension', false, 'csv');

        $this->addElements(array($file));

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'Save Configuration'
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
