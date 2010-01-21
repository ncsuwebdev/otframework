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
 * @package    Ot_LogController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Controller to show logs gathered from the application.
 *
 * @package    Ot_LogController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_LogController extends Zend_Controller_Action
{
    /**
     * Display a list of all users in the system.
     *
     */
    public function indexAction()
    {
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->_helper->pageTitle('ot-log-index:title');  
        $this->view
             ->headScript()
             ->appendFile($this->view->baseUrl()
              . '/public/scripts/ot/jquery.plugin.flexigrid.pack.js');
        $this->view
             ->headLink()
             ->appendStylesheet($this->view->baseUrl()
              . '/public/css/ot/jquery.plugin.flexigrid.css'); 
        
        if ($this->_request->isXmlHttpRequest()) {
                
            $filter = Zend_Registry::get('postFilter');
            
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender();
            
            $log = new Ot_Log();
            
            $sortname  = (isset($filter->sortname)) ? $filter->sortname : 'timestamp';
            $sortorder = (isset($filter->sortorder)) ? $filter->sortorder : 'desc';
            $rp        = (isset($filter->rp)) ? $filter->rp : 40;
            $page      = ((isset($filter->page)) ? $filter->page : 1) - 1;
            $qtype     = (isset($filter->query) && !empty($filter->query)) ? $filter->qtype : null;
            $query     = (isset($filter->query) && !empty($filter->query)) ? $filter->query : null;
            
            $acl = Zend_Registry::get('acl');
            $roles = $acl->getAvailableRoles();
            
            $where = null;
                
            if (!is_null($query)) {
                if ($qtype == 'role') {
                    foreach ($roles as $r) {
                        if ($query == $r['name']) {
                            $query = $r['roleId'];
                            break;
                        }
                    }
                }
                    
                $where = $log->getAdapter()->quoteInto($qtype . ' = ?', $query);
            }
                            
            $logs = $log->fetchAll($where, $sortname . ' ' . $sortorder, $rp,
                $page * $rp);
                            
            $response = array(
                'page' => $page + 1,
                'total' => $log->fetchAll($where)->count(),
                'rows'  => array(),
            );
            
            $config = Zend_Registry::get('config');
                    
            $account = new Ot_Account();
            $accounts = $account->fetchAll(null,
                array('firstName', 'lastName'));
            
            foreach ($accounts as $a) {
                $accountMap[$a->accountId] = $a->firstName . ' ' . $a->lastName;
            }
            
            foreach ($logs as $l) {
                $row = array(
                    'id'   => $l->accountId,
                    'cell' => array(
                        $l->accountId,
                        (isset($accountMap[$l->accountId])) ? $accountMap[$l->accountId] : 'Unknown',
                        (isset($roles[$l->role]['name'])) ? $roles[$l->role]['name'] : $l->role, 
                        $l->request,
                        $l->sid, 
                        strftime($config->user->dateTimeFormat->val, $l->timestamp),
                        $l->message,
                        $l->priorityName,
                        $l->attributeName,
                        $l->attributeId,                                       
                    ),
                );
                
                $response['rows'][] = $row;
            }
            echo Zend_Json::encode($response);
            return;
        }
    }
    
     /**
     * Clear the logs.
     *
     */
    public function clearAction()
    {
        
        $form = Ot_Form_Template::delete('deleteLogForm', 'Clear Logs');                  

        if ($this->_request->isPost() && $form->isValid($_POST)) {
            
            $log = new Ot_Log();
            $log->delete(true);
                        
                $this->_helper->log(Zend_Log::INFO, 'Logs were cleared.');

            $this->_helper->flashMessenger->addMessage('msg-info-logsCleared');
            
            $this->_helper->redirector->gotoRoute(array(
                'controller' => 'log',
                'action' => 'index',
            ), 'ot', true);
        }
        
        $this->_helper->pageTitle('ot-log-clear:title');
        $this->view->form = $form;
    }
}