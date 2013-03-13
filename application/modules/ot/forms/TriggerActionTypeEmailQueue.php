<?php
class Ot_Form_TriggerActionTypeEmailQueue extends Zend_Form_SubForm
{
    public function __construct($options = array())
    {
        parent::__construct($options);

        $to = $this->createElement('text', 'to', array('label' => 'To:'));
        $to->setRequired(true)
           ->setAttrib('maxlength', '255')
           ->setAttrib('size', '40')
           ->addFilter('StripTags')
           ->addFilter('StringTrim')
           ->setDescription('Seperate multiple email addresses by comma.');
           
        $from = $this->createElement('text', 'from', array('label' => 'From Email Address:'));
        $from->setRequired(true)
             ->setAttrib('maxlength', '255')
             ->setAttrib('size', '40')
             ->addFilter('StripTags')
             ->addFilter('StringTrim');
             
        $fromName = $this->createElement('text', 'fromName', array('label' => 'From Display Name:'));
        $fromName->setRequired(false)
                 ->setAttrib('maxlength', '255')
                 ->setAttrib('size', '40')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim');
        
        $subject = $this->createElement('text', 'subject', array('label' => 'Subject:'));
        $subject->setRequired(true)
                ->setAttrib('maxlength', '255')
                ->setAttrib('size', '40')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
                
        $body = $this->createElement('textarea', 'body', array('label' => 'Message:'));
        $body->setRequired(true)
             ->setAttrib('rows', '10')
             ->addFilter('StripTags')
             ->addFilter('StringTrim');
             
        $this->addElements(array($to, $from, $fromName, $subject, $body));  
        
        return $this;
    }
}
