<?php
class Ot_Form_EmailqueueSearch extends Zend_Form
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->setAttrib('id', 'emailqueueSearchForm')
             ->setDecorators(array(
                     'FormElements',
                     array('HtmlTag', array('tag' => 'div', 'class' => 'well')),
                     'Form',
             ))
             ->setMethod(Zend_Form::METHOD_GET);

        $status = $this->createElement('select', 'status', array('label' => 'Sending Status:'));
        $status->addMultiOptions(array(
            'any'     => 'Any Status',
            'waiting' => 'Waiting',
            'sent'    => 'Sent',
            'error'   => 'Error'
        ));
        
        $trigger = $this->createElement('select', 'trigger', array('label' => 'From Trigger:'));
        $trigger->addMultiOption('any', 'Any Trigger');
        
        $ta = new Ot_Model_DbTable_TriggerAction();
        
        $actions = $ta->fetchAll(null, array('eventKey', 'name'));
        
        foreach ($actions as $a) {
            $trigger->addMultiOption($a->triggerActionId, $a->name);
        }
        

        $this->addElements(array($status, $trigger));
            
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
