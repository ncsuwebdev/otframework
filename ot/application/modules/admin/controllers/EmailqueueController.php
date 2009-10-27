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
 * @package    Admin_EmailqueueController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Controller to manage the email queue in the admin section
 *
 * @package    Admin_EmailqueueController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_EmailqueueController extends Zend_Controller_Action
{
    /**
     * Display a list of all users in the system.
     *
     */
    public function indexAction()
    {
        $this->_helper->pageTitle('admin-emailqueue-index:title');  
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ot/scripts/jquery.plugin.flexigrid.pack.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/ot/css/jquery.plugin.flexigrid.css'); 
        
        if ($this->_request->isXmlHttpRequest()) {
        	
        	$filter = Zend_Registry::get('postFilter');
        	
        	$this->_helper->layout->disableLayout();
        	$this->_helper->viewRenderer->setNeverRender();
        	
        	$queue = new Ot_Email_Queue();
        	
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
        		'rows'  => array()
        	);
        	
        	$config = Zend_Registry::get('config');
	        
        	foreach ($emails as $e) {
        		$row = array(
        			'id'   => $e['queueId'],
        			'cell' => array(
        				implode(', ', $e['zendMailObject']->getRecipients()),
                		$e['zendMailObject']->getSubject(),        		
        				ucwords($e['status']),
        				strftime($config->user->dateTimeFormat->val, $e['queueDt']), 
        				($e['sentDt'] == 0) ? 'Not Sent Yet' : strftime($config->user->dateTimeFormat->val, $e['sentDt']),
        				$e['attributeName'],
        				$e['attributeId']     				
        			)
        		);
        		
        		$response['rows'][] = $row;
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
            'index' => $this->_helper->hasAccess('index')
            );
        
        $eq = new Ot_Email_Queue();

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
        $this->_helper->pageTitle('admin-emailqueue-details:title');
    }
}