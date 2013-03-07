<?php
class Ot_Form_ApiApp extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'apiAppForm')
             ->setAttrib('enctype', 'multipart/form-data')
             ;
                           
        $name = $this->createElement('text', 'name', array('label' => 'Application Name:'));
        $name->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '128');
              
        $description = $this->createElement('textarea', 'description', array('label' => 'Description:'));
        $description->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('style', 'height: 100px; width: 350px;');
              
        $website = $this->createElement('text', 'website', array('label' => 'Application Website:', 'placeholder' => 'http://'));
        $website->setRequired(false)
              ->addValidator(new Ot_Validate_Url())
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '255');   
        
        $this->addElements(array($name, $description, $website));
       
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
