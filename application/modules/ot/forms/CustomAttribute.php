<?php
class Ot_Form_CustomAttribute extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_numberOfOptions = 0;
    
    public function setNumberOfOptions($numberOfOptions)
    {
        $this->_numberOfOptions = (int)$numberOfOptions;
    }
    
    public function init()
    {
        $this->setAttrib('id', 'customAttribute');
                  
        // Create and configure username element:
        $label = $this->createElement('text', 'label', array('label' => 'Label:'));
        $label->setRequired(true)
             ->addFilter('StringTrim')
             ->addFilter('StripTags');
        
        $description = $this->createElement('text', 'description', array('label' => 'Description:'));
        $description->addFilter('StringTrim')
             ->addFilter('StripTags');                        
        
        $required = $this->createElement('select', 'required', array('label' => 'Required?'));
        $required->setRequired(true)
                ->setMultiOptions(array(
                    '1' => 'Yes',
                    '0' => 'No',
                ))->setAllowEmpty(false);         
        
        $rowCt = $this->createElement('hidden', 'rowCt');
        $rowCt->setValue($this->_numberOfOptions);
        $rowCt->setDecorators(array('ViewHelper'));
        
        
        $this->addElements(array($label, $description, $required, $rowCt));
        
        if ($this->_numberOfOptions != 0) {
            $container = new Zend_Form_SubForm();
            $container->setDescription('Options:');

            $container->setDecorators(array(
                    array('Description', array('tag' => 'label', 'class' => 'control-label')),
                    array('FormElements'),
                    array(array('controlsWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'control-group', 'id' => 'optionElement')),
                ));

            $container->setElementDecorators(array('ViewHelper', 'FormElements'));

            $this->addSubForm($container, 'options');

            for ($i = 0; $i < $this->_numberOfOptions; $i++) {
                $optionSubform = new Ot_Form_CustomAttributeOption();  

                $container->addSubForm($optionSubform, $i);            
            }            


            $this->addElement('button', 'addElement', array(
                'buttonType'   => Twitter_Bootstrap_Form_Element_Submit::BUTTON_SUCCESS,
                'label'        => 'Add Option',
                'icon'         => 'plus',
                'whiteIcon'    => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            ));
        }
       

        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'Save Attribute'
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
