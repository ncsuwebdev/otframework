<?php
class Ot_Form_ImportConfigCsv extends Zend_Form
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'importConfigCsvForm')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                     'Form',
             ))
             ->setAttrib('enctype', 'multipart/form-data');

        $file = $this->createElement('file', 'config', array('label' => 'Upload a CSV File'));
        $file->addValidator('Count', false, 1)
             ->addValidator('Size', false, 102400)
             ->addValidator('Extension', false, 'csv')
             ->setDecorators(
                    array(
                        'File',
                        'Errors',
                        array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                        array('Label', array('tag' => 'span')),
                    )
                )
             ;

        $this->addElements(array($file));

        $submit = $this->createElement('submit', 'submit', array('label' => 'Import and Overwrite Options'));
        $submit->setDecorators(
            array(
                array('ViewHelper', array('helper' => 'formSubmit'))
            )
        );

        $cancel = $this->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setDecorators(
            array(
                array('ViewHelper', array('helper' => 'formButton'))
            )
        );

        $this->addElements(array($submit, $cancel));

        return $this;

    }
}
