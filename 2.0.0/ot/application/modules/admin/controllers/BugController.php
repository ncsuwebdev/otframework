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
 * @package    Admin_BugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manage bug reports for the application
 *
 * @package    Admin_BugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_BugController extends Zend_Controller_Action 
{    
    /**
     * shows all open bugs
     *
     */
    public function indexAction()
    {
        $bug = new Ot_Bug();

        $bugs = $bug->getBugs();

        $this->view->acl = array(
            'add'     => $this->_helper->hasAccess('add'),
            'details' => $this->_helper->hasAccess('details')
            );

        $this->view->bugs = $bugs->toArray();
        $this->_helper->pageTitle('Bug Reports');
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * shows the details of the bug
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index' => $this->_helper->hasAccess('index'),
            'edit'  => $this->_helper->hasAccess('edit')
            );
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->bugId)) {
            throw new Ot_Exception_Input('Bug ID Not found in query string');
        }

        $bug = new Ot_Bug();

        $thisBug = $bug->find($get->bugId);
        
        if (is_null($thisBug)) {
            throw new Ot_Exception_Data('Bug not found');
        }

        $bt = new Ot_Bug_Text();
        $text = $bt->getBugText($get->bugId)->toArray();
        
        $otAccount = new Ot_Account();
        foreach ($text as &$t) {
            $t['userInfo'] = $otAccount->find($t['accountId'])->toArray();
        }
        
        $this->view->text = $text;

        $this->view->bug = $thisBug->toArray();
        $this->_helper->pageTitle('Bug Details');
    }

    /**
     * adds a bug to the system
     *
     */
    public function addAction()
    {
    	$bug = new Ot_Bug();
    	
    	$form = $bug->form(); 	
             
        $messages = array();
        
        if ($this->_request->isPost()) {

        	if ($form->isValid($_POST)) {
        	    
        	    $time = time();
        	    
	            $data = array(
	                'title'           => $form->getValue('title'),
	                'reproducibility' => $form->getValue('reproducibility'),
	                'severity'        => $form->getValue('severity'),
	                'priority'        => $form->getValue('priority'),
	                'submitDt'        => $time,
	                'status'          => 'new',
	                'text'            => array(
	                    'accountId' => Zend_Auth::getInstance()->getIdentity()->accountId,
	                    'postDt'    => $time,
	                    'text'      => $form->getValue('description'),
	                    ),
	                );
	
	            $bugId = $bug->insert($data);
	            
	            $logOptions = array(
                        'attributeName' => 'bugId',
                        'attributeId'   => $bugId,
                );
                    
                $this->_helper->log(Zend_Log::INFO, 'Bug was added', $logOptions);
                $this->_helper->flashMessenger->addMessage('Bug was submitted successfully');
	
	            $this->_helper->redirector->gotoUrl('/admin/bug/details/?bugId=' . $bugId);
        	} else {
        		$messages[] = 'There was an error processing your form.';
        	}
        }
        
        $this->view->messages = $messages;
        $this->_helper->pageTitle('File Bug Report');
        $this->view->form = $form;

    }
    
    /**
     * Allows a user to edit a bug and add more details to it
     *
     */
    public function editAction()
    {
    	
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->bugId)) {
            throw new Ot_Exception_Input('Bug ID Not found in query string');
        }

        $bug = new Ot_Bug();

        $thisBug = $bug->find($get->bugId);
        
        if (is_null($thisBug)) {
            throw new Ot_Exception_Data('Bug not found');
        }
        
        $form = $bug->form($thisBug->toArray());

        $bt = new Ot_Bug_Text();
        $text = $bt->getBugText($get->bugId)->toArray();
        
        $otAccount = new Ot_Account();
        foreach ($text as &$t) {
            $t['userInfo'] = $otAccount->find($t['accountId'])->toArray();
        }
        
        $this->view->text = $text;
                     
        $messages = array();
        
        if ($this->_request->isPost()) {
        	if ($form->isValid($_POST)) {
        		
	            $data = array(
	                'bugId'             => $get->bugId,
	                'title'             => $form->getValue('title'),
	                'reproducibility'   => $form->getValue('reproducibility'),
	                'severity'          => $form->getValue('severity'),
	                'priority'          => $form->getValue('priority'),
                    'status'            => $form->getValue('status'),
	                );
	
	            $thisBug = $bug->find($data['bugId']);
	            
	            if (is_null($thisBug)) {
	                throw new Exception('Bug not found');
	            }
	            
	            if ($form->getValue('description') != '') {
	            	$data['text'] = array(
	            	    'bugId'     => $get->bugId,
	            	    'postDt'    => time(),
	            	    'accountId' => Zend_Auth::getInstance()->getIdentity()->accountId,
	            	    'text'      => $form->getValue('description'),
	            	);
	            }
	                
	            $bug->update($data, null);
	            
	            $logOptions = array(
                        'attributeName' => 'bugId',
                        'attributeId'   => $get->bugId,
                );
                    
                $this->_helper->log(Zend_Log::INFO, 'Bug was modified', $logOptions);
                $this->_helper->flashMessenger->addMessage('Bug was saved successfully');
	            
	            $this->_helper->redirector->gotoUrl('/admin/bug/details/?bugId=' . $get->bugId);
        	} else {
        		$messages[] = 'There were problems processing the form.';
        	}

        }
        
        $this->_helper->pageTitle('Edit Bug');
        $this->view->messages = $messages;
        $this->view->form     = $form;
    }    
}
