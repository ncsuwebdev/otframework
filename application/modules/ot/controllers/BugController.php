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
 * @package    Ot_BugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manage bug reports for the application
 *
 * @package    Ot_BugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_BugController extends Zend_Controller_Action
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
            'edit'    => $this->_helper->hasAccess('edit'),
            'delete'  => $this->_helper->hasAccess('delete'),
            'details' => $this->_helper->hasAccess('details'),
        );

        $this->view->bugs = $bugs->toArray();
        $this->_helper->pageTitle('ot-bug-index:title');
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * shows the details of the bug
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'  => $this->_helper->hasAccess('index'),
            'edit'   => $this->_helper->hasAccess('edit'),
            'delete' => $this->_helper->hasAccess('delete'),
        );
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->bugId)) {
            throw new Ot_Exception_Input('msg-error-bugIdNotFound');
        }

        $bug = new Ot_Bug();

        $thisBug = $bug->find($get->bugId);
        
        if (is_null($thisBug)) {
            throw new Ot_Exception_Data('msg-error-noBug');
        }

        $bt = new Ot_Bug_Text();
        $text = $bt->getBugText($get->bugId)->toArray();
        
        $otAccount = new Ot_Account();
        foreach ($text as &$t) {
            $t['userInfo'] = $otAccount->find($t['accountId'])->toArray();
        }
        
        $this->view->text = $text;

        $this->view->bug = $thisBug->toArray();
        $this->_helper->pageTitle('ot-bug-details:title');
    }
    
    /**
     * deletes a bug from the system
     */
    public function deleteAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->bugId)) {
            throw new Ot_Exception_Input('msg-error-bugIdNotFound');
        }

        $bug = new Ot_Bug();

        $thisBug = $bug->find($get->bugId);
        
        if (is_null($thisBug)) {
            throw new Ot_Exception_Data('msg-error-noBug');
        }
        
        $form = Ot_Form_Template::delete('deleteBug');
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
                
            $bug->delete($get->bugId);
            
            $logOptions = array(
                'attributeName' => 'bugId',
                'attributeId'   => $get->bugId,
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Bug was deleted', $logOptions);
            $this->_helper->flashMessenger->addMessage('msg-info-bugDeleted');
            
            $this->_helper
                 ->redirector
                 ->gotoRoute(array('controller' => 'bug'), 'ot', true);
            
        }
        
        $this->_helper->pageTitle('ot-bug-delete:title');
        $this->view->form     = $form;        
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
                        'accountId' => Zend_Auth::getInstance()->getIdentity()
                                                               ->accountId,
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
                $this->_helper->flashMessenger->addMessage('msg-info-bugSubmitted');
        
                $this->_helper->redirector->gotoRoute(
                    array(
                        'controller' => 'bug',
                        'action' => 'details',
                        'bugId' => $bugId,
                    ),
                    'ot',
                    true
                );
            } else {
                $messages[] = 'msg-error-formError';
            }
        }
        
        $this->view->messages = $messages;
        $this->_helper->pageTitle('ot-bug-add:title');
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
            throw new Ot_Exception_Input('msg-error-bugIdNotFound');
        }

        $bug = new Ot_Bug();

        $thisBug = $bug->find($get->bugId);
        
        if (is_null($thisBug)) {
            throw new Ot_Exception_Data('msg-error-noBug');
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
                    throw new Exception('msg-error-noBug');
                }
                
                if ($form->getValue('description') != '') {
                    $data['text'] = array(
                        'bugId'     => $get->bugId,
                        'postDt'    => time(),
                        'accountId' => Zend_Auth::getInstance()->getIdentity()
                                                               ->accountId,
                        'text'      => $form->getValue('description'),
                    );
                }
                    
                $bug->update($data, null);
                
                $logOptions = array(
                    'attributeName' => 'bugId',
                    'attributeId'   => $get->bugId,
                );
                
                $this->_helper
                     ->log(Zend_Log::INFO, 'Bug was modified', $logOptions);
                $this->_helper
                     ->flashMessenger->addMessage('msg-info-bugUpdated');
                
                $this->_helper->redirector->gotoRoute(
                    array(
                        'controller' => 'bug',
                        'action' => 'details',
                        'bugId' => $get->bugId
                    ),
                    'ot',
                    true
                );
            } else {
                $messages[] = 'msg-error-formError';
            }

        }
        
        $this->_helper->pageTitle('ot-bug-edit:title');
        $this->view->messages = $messages;
        $this->view->form     = $form;
    }    
}
