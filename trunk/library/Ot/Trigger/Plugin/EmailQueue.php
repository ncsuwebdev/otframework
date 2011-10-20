<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Trigger_Plugin_EmailQueue
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Trigger plugin to queue an email when an action happens
 *
 * @package    Ot_Trigger_Plugin_EmailQueue
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Trigger_Plugin_EmailQueue implements Ot_Plugin_Interface
{
    /**
     * table name for references
     *
     * @var string
     */
    protected $_name = 'tbl_ot_trigger_helper_emailqueue';
    
    public function __construct()
    {
        $config = Zend_Registry::get('config');
        
        if (isset($config->app->tablePrefix) && !empty($config->app->tablePrefix)) {
            $this->_name = $config->app->tablePrefix . $this->_name;
        }
    }
    
    /**
     * Subform to add a new trigger
     *
     * @return Zend_Form element
     */
    public function addSubForm()
    {
        $description = 'This email will be sent using the email queue, rather than being sent immediately.';
        
        $form = $this->_getForm();
        $form->setDescription($description);
        
        return $form;
    }
    
    /**
     * Action called when the addForm is processed
     *
     * @param array $data
     */
    public function addProcess($data)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        $dba->insert($this->_name, $data);
    }
    
    /**
     * Subform to edit an existing trigger
     *
     * @param mixed $id
     * @return Zend_Form element
     */
    public function editSubForm($id)
    {
        $data = $this->get($id);
        
        $description = 'This email will be sent using the email queue, rather than being sent immediately.';
        
        $form = $this->_getForm($data);
        $form->setDescription($description);
                
        return $form;
    }
    
    /**
     * Action called when the editForm is processed
     *
     * @param array $data
     */
    public function editProcess($data)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        
        $where = $dba->quoteInto('triggerActionId = ?', $data['triggerActionId']);
        
        $dba->update($this->_name, $data, $where);        
    }
    
    /**
     * Action called when a request is processed to delete a trigger
     *
     * @param mixed $id
     * @return boolean
     */
    public function deleteProcess($id)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        
        $where = $dba->quoteInto('triggerActionId = ?', $id);

        return $dba->delete($this->_name, $where);
    }
    
    /**
     * retrieves trigger with a specific ID
     *
     * @param mixed $id
     * @return Zend_Db_Table_Rowset or null
     */
    public function get($id)
    {
        $dba = Zend_Db_Table::getDefaultAdapter();
        
        $select = $dba->select();

        $select->from($this->_name)->where('triggerActionId = ?', $id);

        $result = $dba->fetchAll($select);

        if (count($result) == 1) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Action called when a trigger is executed.
     *
     * @param array $data
     */
    public function dispatch($data)
    {
        $eq = new Ot_Email_Queue();
        
        $mail = new Zend_Mail();

        $to = explode(',', $data['to']);
        array_walk($to, 'trim');

        foreach ($to as $t) {
            $mail->addTo($t);
        }
        
        $mail->setFrom($data['from'], $data['fromName']);
        $mail->setSubject($data['subject']);
        $mail->setBodyText($data['body']);
        
        $eData = array(
            'zendMailObject' => $mail,
            'attributeName'  => 'triggerActionId',
            'attributeId'    => $data['triggerActionId'],
        );
        
        $eq->queueEmail($eData);
    }
    
    /**
     * Creates a form object
     *
     * @param array $data
     * @return Zend_Form
     */
    protected function _getForm($data = array())
    {        
        $form = new Zend_Form_SubForm();
        $form->setDecorators(
            array(
                'Description',
                'FormElements',
                array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
            )
        );
        
        $to = $form->createElement('text', 'to', array('label' => 'To:'));
        $to->setRequired(true)
           ->setAttrib('maxlength', '255')
           ->setAttrib('size', '40')
           ->addFilter('StripTags')
           ->addFilter('StringTrim');
           
        if (isset($data['to'])) {
            $to->setValue($data['to']);
        }
           
        $from = $form->createElement('text', 'from', array('label' => 'From Address:'));
        $from->setRequired(true)
             ->setAttrib('maxlength', '255')
             ->setAttrib('size', '40')
             ->addFilter('StripTags')
             ->addFilter('StringTrim');
             
        if (isset($data['from'])) {
            $from->setValue($data['from']);
        }
        
        $fromName = $form->createElement('text', 'fromName', array('label' => 'From Name:'));
        $fromName->setRequired(false)
                 ->setAttrib('maxlength', '255')
                 ->setAttrib('size', '40')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim');
        
        if (isset($data['fromName'])) {
            $fromName->setValue($data['fromName']);
        }
           
        $subject = $form->createElement('text', 'subject', array('label' => 'Subject:'));
        $subject->setRequired(true)
                ->setAttrib('maxlength', '255')
                ->setAttrib('size', '40')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
                
        if (isset($data['subject'])) {
            $subject->setValue($data['subject']);
        }
        
        $body = $form->createElement('textarea', 'body', array('label' => 'Message:'));
        $body->setRequired(true)
             ->setAttrib('rows', '10')
             ->addFilter('StripTags')
             ->addFilter('StringTrim');
             
        if (isset($data['body'])) {
            $body->setValue($data['body']);
        }              
        
        $form->addElements(array($to, $from, $fromName, $subject, $body))->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                array('Label', array('tag' => 'span')),
            )
        );

        return $form;        
    }
}