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
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Bug reports
 *
 * @package    Admin_BugController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Admin_BugController extends Internal_Controller_Action 
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
            'add'     => $this->_acl->isAllowed($this->_role, $this->_resource, 'add'),
            'details' => $this->_acl->isAllowed($this->_role, $this->_resource, 'details'),
            );

        if ($bugs->count() != 0) {
            $this->view->javascript = 'sortable.js';
        }

        $this->view->bugs = $bugs->toArray();
        $this->view->title = 'Bug Reports';
    }

    /**
     * shows the details of the bug
     *
     */
    public function detailsAction()
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

        $bt = new Ot_Bug_Text();
        $this->view->text = $bt->getBugText($get->bugId)->toArray();
        
        $this->view->acl = array(
            'edit' => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            );

        $this->view->bug = $thisBug->toArray();
        $this->view->title = 'Bug Details';
    }

    /**
     * adds a bug to the system
     *
     */
    public function addAction()
    {
    	$bug = new Ot_Bug();
    	
    	$form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'login')
             ;
        
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title:')
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setRequired(true)
              ->setAttrib('maxlength', '64')
              ;
              
        $reproducibility = new Zend_Form_Element_Select('reproducibility');
        $reproducibility->setLabel('Reproducibility:');
        $reproducibility->addMultiOptions($bug->getColumnOptions('reproducibility'));
        
        $severity = new Zend_Form_Element_Select('severity');
        $severity->setLabel('Severity:');
        $severity->addMultiOptions($bug->getColumnOptions('severity'));
        
        $priority = new Zend_Form_Element_Select('priority');
        $priority->setLabel('Priority:');
        $priority->addMultiOptions($bug->getColumnOptions('priority'));
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description:')
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ->setRequired(true)
                    ->setAttrib('rows', '5')
                    ->setAttrib('cols', '80')
                    ;
        
        $form->addElements(array($title, $reproducibility, $severity, $priority, $description))
             ->addDisplayGroup(array('title', 'reproducibility', 'severity', 'priority', 'description'), 'fields')
             ->addElement('submit', 'submitButton', array('label' => 'Submit Bug'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;    	
             
        $messages = array();
        
        if ($this->_request->isPost()) {
        	
        	if ($form->isValid($_POST)) {
	            $data = array(
	                'title'           => $form->getValue('title'),
	                'reproducibility' => $form->getValue('reproducibility'),
	                'severity'        => $form->getValue('severity'),
	                'priority'        => $form->getValue('priority'),
	                'submitDt'        => time(),
	                'status'          => 'new',
	                'text'            => array(
	                    'userId' => Zend_Auth::getInstance()->getIdentity(),
	                    'postDt' => time(),
	                    'text'   => $form->getValue('description'),
	                    ),
	                );
	                
	            $bug = new Ot_Bug;
	
	            $bugId = $bug->insert($data);
	            
	            $this->_logger->setEventItem('attributeName', 'bugId');
	            $this->_logger->setEventItem('attributeId', $bugId);
	            $this->_logger->info('Bug was added');
	
	            $this->_helper->redirector->gotoUrl('/admin/bug/details/?bugId=' . $bugId);
        	} else {
        		$messages[] = 'There was an error processing your form.';
        	}
        	
        }
        
        $this->view->messages = $messages;
        $this->view->title = 'File Bug Report';
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

        $bt = new Ot_Bug_Text();
        $this->view->text = $bt->getBugText($get->bugId)->toArray();

        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'login')
             ;
        
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title:')
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setRequired(true)
              ->setAttrib('maxlength', '64')
              ->setValue($thisBug->title)
              ;
              
        $status = new Zend_Form_Element_Select('status');
        $status->setLabel('Status:');
        $status->addMultiOptions($bug->getColumnOptions('status'));
        $status->setValue($thisBug->status);
        
        $reproducibility = new Zend_Form_Element_Select('reproducibility');
        $reproducibility->setLabel('Reproducibility:');
        $reproducibility->addMultiOptions($bug->getColumnOptions('reproducibility'));
        $reproducibility->setValue($thisBug->reproducibility);
        
        $severity = new Zend_Form_Element_Select('severity');
        $severity->setLabel('Severity:');
        $severity->addMultiOptions($bug->getColumnOptions('severity'));
        $severity->setValue($thisBug->severity);
        
        $priority = new Zend_Form_Element_Select('priority');
        $priority->setLabel('Priority:');
        $priority->addMultiOptions($bug->getColumnOptions('priority'));
        $priority->setValue($thisBug->priority);
        
        $description = new Zend_Form_Element_Textarea('text');
        $description->setLabel('Add Text:')
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ->setAttrib('rows', '5')
                    ->setAttrib('cols', '80')
                    ;
        
        $form->addElements(array($title, $status, $reproducibility, $severity, $priority, $description))
             ->addDisplayGroup(array('title', 'status', 'reproducibility', 'severity', 'priority', 'text'), 'fields')
             ->addElement('submit', 'submitButton', array('label' => 'Submit Bug'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;      
                     
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
	
	            $bug = new Ot_Bug;
	
	            $thisBug = $bug->find($data['bugId']);
	            
	            if (is_null($thisBug)) {
	                throw new Exception('Bug not found');
	            }
	            
	            if ($form->getValue('text') != '') {
	            	$data['text'] = array(
	            	    'bugId'  => $get->bugId,
	            	    'postDt' => time(),
	            	    'userId' => Zend_Auth::getInstance()->getIdentity(),
	            	    'text'   => $form->getValue('text'),
	            	);
	            }
	                
	            $bug->update($data, null);
	            
                $this->_logger->setEventItem('attributeName', 'bugId');
                $this->_logger->setEventItem('attributeId', $get->bugId);
                $this->_logger->info('Bug was modified');	            
	            
	            $this->_helper->redirector->gotoUrl('/admin/bug/details/?bugId=' . $get->bugId);
        	} else {
        		$messages[] = 'There were problems processing the form.';
        	}

        }
        
        $this->view->title = 'Edit Bug';
        $this->view->messages = $messages;
        $this->view->form = $form;
    }    
}
