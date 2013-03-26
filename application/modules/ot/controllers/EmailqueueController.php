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
 * @package    Ot_EmailqueueController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Controller to manage the email queue in the admin section
 *
 * @package    Ot_EmailqueueController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_EmailqueueController extends Zend_Controller_Action
{
    /**
     * Display a list of all users in the system.
     *
     */
    public function indexAction()
    {            
        
        $filterStatus = $this->_getParam('status', 'any');
        $filterTrigger = $this->_getParam('trigger', 'any');

        $filterSort = $this->_getParam('sort', 'queueDt');
        $filterDirection = $this->_getParam('direction', 'asc');

        $form = new Ot_Form_EmailqueueSearch();
        $form->populate($_GET);
        
        $eq = new Ot_Model_DbTable_EmailQueue();
            
        $select = new Zend_Db_Table_Select($eq);

        if ($filterStatus != '' && $filterStatus != 'any') {
            $select->where('status = ?', $filterStatus);
        }
        
        if ($filterTrigger != '' && $filterTrigger != 'any') {
            $select->where('attributeName = ?', 'triggerActionId');
            $select->where('attributeId = ?', $filterTrigger);
        }

        $select->order($filterSort . ' ' . $filterDirection);        
        
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);

        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
                                
        
        $ta = new Ot_Model_DbTable_TriggerAction();
        
        $actions = $ta->fetchAll();
        
        $triggers = array();
        foreach ($actions as $a) {
            $triggers[$a->triggerActionId] = $a;
        }
        
        $this->_helper->pageTitle('ot-emailqueue-index:title'); 
        
        $this->view->assign(array(
            'paginator'     => $paginator,
            'form'          => $form,
            'interface'     => true,
            'sort'          => $filterSort,
            'direction'     => $filterDirection,
            'triggers'      => $triggers,
        ));        
    }        
    
    /**
     * Shows the details of an email that is in the queue
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'  => $this->_helper->hasAccess('index'),
            'delete' => $this->_helper->hasAccess('delete'),
        );
        
        $eq = new Ot_Model_DbTable_EmailQueue();

        $queueId = $this->_getParam('queueId', null);        
        if (is_null($queueId)) {
            throw new Ot_Exception_Input('msg-error-queueIdNotSet');
        }

        $email = $eq->find($queueId);
        if (is_null($email)) {
            throw new Ot_Exception_Data('msg-error-noQueue');
        }

        $email['msg'] = array(
            'to'      => implode(', ', $email['zendMailObject']->getRecipients()),
            'from'    => $email['zendMailObject']->getFrom(),
            'subject' => $email['zendMailObject']->getSubject(),
            'body'    => nl2br(quoted_printable_decode($email['zendMailObject']->getBodyText(true))),
            'header'  => $email['zendMailObject']->getHeaders(),
        );

        $this->view->assign(array(
            'email' => $email
        ));
        
        $this->_helper->pageTitle('ot-emailqueue-details:title');
    }
    
    
    /**
     * Deletes an email from the queue
     */
    public function deleteAction()
    {
        $eq = new Ot_Model_DbTable_EmailQueue();

        $queueId = $this->_getParam('queueId', null);        
        if (is_null($queueId)) {
            throw new Ot_Exception_Input('msg-error-queueIdNotSet');
        }

        $email = $eq->find($queueId);
        if (is_null($email)) {
            throw new Ot_Exception_Data('msg-error-noQueue');
        }
        
        if ($this->_request->isPost()) {
                
            $where = $eq->getAdapter()->quoteInto('queueId = ?', $queueId);
            $eq->delete($where);
            
            $this->_helper->messenger->addSuccess('ot-emailqueue-delete:success');
            $this->_helper->redirector->gotoRoute(array('controller' => 'emailqueue'), 'ot', true);
        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }        
    }
}