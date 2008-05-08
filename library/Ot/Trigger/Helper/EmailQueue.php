<?php

class Ot_Trigger_Helper_EmailQueue implements Ot_Trigger_Helper_Interface 
{
	protected $_name = 'tbl_ot_trigger_helper_emailqueue';
	
	public function addSubForm()
	{
		$description = 'Create an email template to be sent to users when a trigger is executed.';
		
		$form = $this->_getForm();
		$form->setDescription($description);
		
		return $form;
	}
	
	public function addProcess($data)
	{
		$dba = Zend_Registry::get('dbAdapter');
		$dba->insert($this->_name, $data);
	}
	
	public function editSubForm($triggerActionId)
	{
		$data = $this->get($triggerActionId);
		
        $description = 'Modify the email template to be sent to users when a trigger is executed.';
        
        $form = $this->_getForm($data);
        $form->setDescription($description);
        		
		return $form;
	}
	
	public function editProcess($data)
	{
        $dba = Zend_Registry::get('dbAdapter');
        
        $where = $dba->quoteInto('triggerActionId = ?', $data['triggerActionId']);
        
        $dba->update($this->_name, $data, $where);		
	}
	
	public function deleteProcess($triggerActionId)
	{
		$dba = Zend_Registry::get('dbAdapter');
		
		$where = $dba->quoteInto('triggerActionId = ?', $triggerActionId);

        return $dba->delete($this->_name, $where);
	}
	
	public function get($triggerActionId)
	{
		$dba = Zend_Registry::get('dbAdapter');
		
        $select = $dba->select();

        $select->from($this->_name)
               ->where('triggerActionId = ?', $triggerActionId);

        $result = $dba->fetchAll($select);

        if (count($result) == 1) {
        	return $result[0];
        }
        
        return null;
	}
	
	public function dispatch($data)
	{
		$eq = new Ot_Email_Queue();
		
		$mail = new Zend_Mail();
		$mail->addTo($data['to']);
		$mail->setFrom($data['from']);
		$mail->setSubject($data['subject']);
		$mail->setBodyText($data['body']);
		
		$eData = array(
		    'zendMailObject' => $mail,
		    'attributeName'  => 'triggerActionId',
		    'attributeId'    => $data['triggerActionId'],
		);
		
		$eq->queueEmail($eData);
	}
	
	protected function _getForm($data = array())
	{        
        $form = new Zend_Form_SubForm();
        
        $decorators = $form->getDecorators();
        $decorators = array_merge(array(new Zend_Form_Decorator_Description()), $decorators);

        $form->clearDecorators();
        $form->setDecorators($decorators);
        
        $to = $form->createElement('text', 'to', array('label' => 'To:'));
        $to->setRequired(true)
           ->setAttrib('maxlength', '255')
           ->setAttrib('size', '40')
           ->addFilter('StripTags')
           ->addFilter('StringTrim')
           ;
        if (isset($data['to'])) {
        	$to->setValue($data['to']);
        }
           
        $from = $form->createElement('text', 'from', array('label' => 'From:'));
        $from->setRequired(true)
             ->setAttrib('maxlength', '255')
             ->setAttrib('size', '40')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ;
	    if (isset($data['from'])) {
            $from->setValue($data['from']);
        }
           
        $subject = $form->createElement('text', 'subject', array('label' => 'Subject:'));
        $subject->setRequired(true)
                ->setAttrib('maxlength', '255')
                ->setAttrib('size', '40')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ;
	    if (isset($data['subject'])) {
            $subject->setValue($data['subject']);
        }
        
        $body = $form->createElement('textarea', 'body', array('label' => 'Message:'));
        $body->setRequired(true)
             ->setAttrib('rows', '10')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ;  
	    if (isset($data['body'])) {
            $body->setValue($data['body']);
        }              
        
        $form->addElements(array($to, $from, $subject, $body));
        $form->addDisplayGroup(array('to', 'from', 'subject', 'body'), 'email');

        return $form;		
	}
}