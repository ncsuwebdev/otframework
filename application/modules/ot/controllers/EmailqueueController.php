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
        $this->_helper->pageTitle('ot-emailqueue-index:title');  
        $this->view
             ->headScript()
             ->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.flexigrid.pack.js');
        $this->view
             ->headLink()
             ->appendStylesheet($this->view->baseUrl() . '/public/css/ot/jquery.plugin.flexigrid.css');
        $this->view->messages = $this->_helper->messenger->getMessages(); 
        
        if ($this->_request->isXmlHttpRequest()) {
                
            $filter = Zend_Registry::get('postFilter');
            
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender();
            
            $queue = new Ot_Model_DbTable_EmailQueue();
            
            $sortname  = (isset($filter->sortname)) ? $filter->sortname : 'queueDt';
            $sortorder = (isset($filter->sortorder)) ? $filter->sortorder : 'desc';
            $rp        = (isset($filter->rp)) ? $filter->rp : 40;
            $page      = ((isset($filter->page)) ? $filter->page : 1) - 1;
            $qtype     = (isset($filter->query) && !empty($filter->query)) ? $filter->qtype : null;
            $query     = (isset($filter->query) && !empty($filter->query)) ? $filter->query : null;
            
            
            $where = null;
            if (!is_null($query)) {
                $where = $queue->getAdapter()->quoteInto($qtype . ' = ?', $query);
            }
                            
            $emails = $queue->fetchAll($where, $sortname . ' ' . $sortorder, $rp, $page * $rp);
                            
            $response = array(
                'page' => $page + 1,
                'total' => count($queue->fetchAll($where)),
                'rows'  => array(),
            );
            
            $registry = new Ot_Var_Register();
            
            foreach ($emails as $e) {
                if(gettype($e['zendMailObject']) == 'object' && get_class($e['zendMailObject']) == 'Zend_Mail') {
                    if ($this->_helper->hasAccess('details')) {
                        $recipientField = '<a href="' . $this->view->url(
                            array(
                                'controller' => 'emailqueue',
                                'action'     => 'details',
                                'queueId'    => $e['queueId'],
                            ),
                            'ot',
                            true
                        )
                        . '">' . implode(', ', $e['zendMailObject']->getRecipients()) . '</a>';
                    } else {
                        $recipientField = implode(', ', $e['zendMailObject']->getRecipients());
                    }
                    
                    $row = array(
                        'id'   => $e['queueId'],
                        'cell' => array(
                            $recipientField,
                            $e['zendMailObject']->getSubject(),                        
                            ucwords($e['status']),
                            strftime($registry->dateTimeFormat->getValue(), $e['queueDt']),
                            ($e['sentDt'] == 0)
                                ? 'Not Sent Yet' : strftime($registry->dateTimeFormat->getValue(), $e['sentDt']),
                            $e['attributeName'],
                            $e['attributeId'],
                        )
                    );
                    
                    $response['rows'][] = $row;
                } else {
                    // if an email breaks, fill it with error text instead of just killing the pageload
                    $response['rows'][] = array(
                        'id' => '0',
                        'cell' => array(
                            'Email Queue Error',
                            'Zend_Mail Error',
                            'Corrupt',
                            'Unknown',
                            'Unknown',
                            $e['attributeName'],
                            $e['attributeId'],
                        ),
                    );
                }
            }
            echo Zend_Json::encode($response);
            return;
        }
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

        $get = Zend_Registry::get('getFilter');

        if (!isset($get->queueId)) {
            throw new Ot_Exception_Input('msg-error-queueIdNotSet');
        }

        $email = $eq->find($get->queueId);

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

        $this->view->email = $email;
        $this->view->registry = new Ot_Var_Register();
        $this->_helper->pageTitle('ot-emailqueue-details:title');
    }
    
    
    /**
     * Deletes an email from the queue
     */
    public function deleteAction()
    {
        $eq = new Ot_Model_DbTable_EmailQueue();

        $get = Zend_Registry::get('getFilter');

        if (!isset($get->queueId)) {
            throw new Ot_Exception_Input('msg-error-queueIdNotSet');
        }

        $email = $eq->find($get->queueId);

        if (is_null($email)) {
            throw new Ot_Exception_Data('msg-error-noQueue');
        }
        
        $form = Ot_Form_Template::delete('deleteEmail');

        if ($this->_request->isPost() && $form->isValid($_POST)) {
                
            $where = $eq->getAdapter()->quoteInto('queueId = ?', $get->queueId);
            $eq->delete($where);
            
            $this->_helper->messenger->addSuccess('ot-emailqueue-delete:success');
            $this->_helper->redirector->gotoRoute(array('controller' => 'emailqueue'), 'ot', true);
        }
        
        $this->view->form = $form;
        $this->_helper->pageTitle('ot-emailqueue-delete:pageTitle');
    }
}