<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Admin_TriggerController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manages the triggers that are dispatched from the application based on an event.
 *
 * @package    Admin_TriggerController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_TriggerController extends Internal_Controller_Action  
{	
	/**
	 * Trigger config file
	 *
	 * @var Zend_Config
	 */
	protected $_triggerConfig = '';
	
	/**
	 * Flash Messenger object
	 *
	 * @var unknown_type
	 */
	protected $_flashMessenger = null;
	
	/**
	 * Setup controller vars
	 *
	 */
	public function init()
	{
		$this->_flashMessenger = $this->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('trigger');
        
		$files = Zend_Registry::get('configFiles');
        $this->_triggerConfig = new Zend_Config_Xml($files['trigger'], 'production');
        
        parent::init();
	}
	
    /**
     * Shows all availabe triggers
     */
    public function indexAction()
    {
        $this->view->title = "Trigger Index";
        
        $this->view->acl = array(
            'details'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'details'),
            );

        $triggers = array();
        foreach ($this->_triggerConfig->triggers as $key => $value) {

        	$triggers[] = array(
        	   'name' => $key,
        	   'description' => $value->description,
        	);
        }
        
        if (count($triggers) != 0) {
            $this->view->javascript = array(
               'sortable.js',
            );
        }        
        
        $this->view->triggers = $triggers;
    }
    
    /**
     * Shows the actions for the selected trigger
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'add'    => $this->_acl->isAllowed($this->_role, $this->_resource, 'add'),
            'edit'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete' => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete'),
            );        
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerId)) {
        	throw new Ot_Exception_Input('Trigger ID not found in query string.');
        }
        
        if (!($this->_triggerConfig->triggers->{$get->triggerId} instanceof Zend_Config)) {
            throw new Ot_Exception_Data('Trigger does not exist in configuration file.');
        }
        
        $this->view->triggerId = $get->triggerId;
        $this->view->triggerDescription = $this->_triggerConfig->triggers->{$get->triggerId}->description;
        $this->view->title = "Trigger Actions for " . $get->triggerId;
        
        $action = new Ot_Trigger_Action();

        $where = $action->getAdapter()->quoteInto('triggerId = ?', $get->triggerId);
        $actions = $action->fetchAll($where)->toArray();
        
        $config = Zend_Registry::get('appConfig');
        
        foreach ($actions as &$a) {
        	$a['helper'] = $config->triggerPlugins->{$a['helper']};
        }
        
        if (count($actions) != 0) {
        	$this->view->javascript = array(
        	   'sortable.js',
        	);
        }
        $this->view->actions = $actions;
        
        $this->view->messages = $this->_flashMessenger->getMessages();
    }
    
    /**
     * Add a new action to the trigger
     *
     */
    public function addAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerId)) {
            throw new Ot_Exception_Input('Trigger ID not found in query string.');
        }
        
        if (!($this->_triggerConfig->triggers->{$get->triggerId} instanceof Zend_Config)) {
            throw new Ot_Exception_Data('Trigger does not exist in configuration file.');
        }
        
        $this->view->triggerId = $get->triggerId;
        $this->view->triggerDescription = $this->_triggerConfig->triggers->{$get->triggerId}->description;
        $this->view->title = "Select Trigger Type";

        $config = Zend_Registry::get('appConfig');
        $types = array();
        
        foreach ($config->triggerPlugins as $key => $value) {
        	$types[$key] = $value;
        }
        
        if (count($types) == 0) {
        	throw new Ot_Exception_Data('No helpers are defined in the application config file.');
        }
        
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'addAction')
             ;
        
        $helpers = new Zend_Form_Element_Select('helper');
        $helpers->setLabel('Action:')
                ->addMultiOptions($types)
                ->setValue($get->helper)
                ;
                
        $hidden = new Zend_Form_Element_Hidden('triggerId');
        $hidden->setValue($get->triggerId);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
                  
        // Create and configure username element:
        $name = $form->createElement('text', 'name', array('label' => 'Shortcut Name:'));
        $name->setRequired(true)
             ->addFilter('StringTrim');
        
        $form->addElements(array($hidden, $helpers, $name));
        $form->addDisplayGroup(array('triggerId', 'helper', 'name'), 'fields'); 
        
        if (isset($get->helper)) {
        	$obj = $get->helper;
        } else {
        	$obj = key($types);
        }
        	
        $thisHelper = new $obj;

        $subForm = $thisHelper->addSubForm();
        $form->addSubForm($subForm, $obj);
        
        $submit = $form->createElement('submit', 'nextButton', array('label' => 'Save Action'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));        
        
        $form->addElements(array($submit, $cancel));        
             
        $messages = array();
        if ($this->_request->isPost()) {
        	if ($form->isValid($_POST)) {
        		$action = new Ot_Trigger_Action();
        		
        		$data = array(
        		  'triggerId' => $get->triggerId,
        		  'name'      => $form->getValue('name'),
        		  'helper'    => $form->getValue('helper'),
        		);
        		
        		$triggerActionId = $action->insert($data);
        		
        		$subData = array(
        		  'triggerActionId' => $triggerActionId,
        		);

        		$elements = $subForm->getElements();
        		foreach ($elements as $key => $value) {
        			$subData[$key] = $subForm->getValue($key);
        		}
        		
        		$thisHelper->addProcess($subData);
        		
        		$this->_logger->setEventItem('attributeName', 'triggerActionId');
                $this->_logger->setEventItem('attributeId', $triggerActionId);
                $this->_logger->info('Trigger Action added'); 
        		
        		$this->_flashMessenger->addMessage('The action was added successfully.');
        		
        		$this->_helper->redirector->gotoUrl('/admin/trigger/details/?triggerId=' . $get->triggerId);
        		
        	} else {
        		$messages[] = 'There was an error processing the form';
        	}
        }
        
        $vars = array();
        foreach ($this->_triggerConfig->triggers->{$get->triggerId}->vars as $key => $value) {
            $vars[$key] = $value;
        }
        
        if (count($vars) != 0) {
            $this->view->javascript = array(
               'sortable.js',
            );
        } 
                
        $this->view->messages = $messages;
        $this->view->templateVars = $vars;        
        $this->view->form = $form;
    }

    /**
     * Edit an existing action of a trigger
     *
     */
    public function editAction()
    {       
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerActionId)) {
            throw new Ot_Exception_Input('Trigger Action ID not found in query string.');
        }
        
        $action = new Ot_Trigger_Action();
        
        $thisAction = $action->find($get->triggerActionId);
        
        if (is_null($thisAction)) {
        	throw new Ot_Exception_Data('Trigger action does not exist.');
        }
        
        $triggerId = $thisAction->triggerId;
        
        if (!($this->_triggerConfig->triggers->{$triggerId} instanceof Zend_Config)) {
            throw new Ot_Exception_Data('Trigger does not exist in configuration file.');
        }
        
        $this->view->triggerId = $triggerId;
        $this->view->triggerDescription = $this->_triggerConfig->triggers->{$triggerId}->description;
        $this->view->title = "Select Trigger Type";

        $config = Zend_Registry::get('appConfig');
        
        if (!isset($config->triggerPlugins->{$thisAction->helper})) {
            throw new Ot_Exception_Data('Trigger Helper not found');
        }
        
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'editAction')
             ;
                
        $hidden = new Zend_Form_Element_Hidden('triggerActionId');
        $hidden->setValue($get->triggerActionId);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
        
        $helperStatic = $form->createElement('text', 'helperStatic', array('label' => 'Action:'));
        $helperStatic->setValue($config->triggerPlugins->{$thisAction->helper})
                    ->setAttrib('size', '40')
                    ->setAttrib('readonly', true)
                    ;        
                  
        // Create and configure username element:
        $name = $form->createElement('text', 'name', array('label' => 'Shorcut Name:'));
        $name->setRequired(true)
             ->addFilter('StringTrim')
             ->setValue($thisAction->name);
        
        $form->addElements(array($hidden, $helperStatic, $name));
        $form->addDisplayGroup(array('triggerId', 'helperStatic', 'name'), 'fields'); 
        
        $obj = $thisAction->helper;
        
        $thisHelper = new $obj;

        $subForm = $thisHelper->editSubForm($get->triggerActionId);
        
        $form->addSubForm($subForm, $obj);
        
        $submit = $form->createElement('submit', 'nextButton', array('label' => 'Save Action'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));        
                        
        $form->addElements(array($submit, $cancel));        
             
        $messages = array();
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $action = new Ot_Trigger_Action();
                
                $data = array(
                  'triggerActionId' => $get->triggerActionId,
                  'name'            => $form->getValue('name'),
                );
                
                $action->update($data, null);
                
                $subData = array(
                  'triggerActionId' => $get->triggerActionId,
                );

                $elements = $subForm->getElements();
                foreach ($elements as $key => $value) {
                    $subData[$key] = $subForm->getValue($key);
                }
                
                $thisHelper->editProcess($subData);
                
                $this->_logger->setEventItem('attributeName', 'triggerActionId');
                $this->_logger->setEventItem('attributeId', $get->triggerActionId);
                $this->_logger->info('Trigger Action modified');  
            
                $this->_flashMessenger->addMessage('The action was modified successfully.');
                
                $this->_helper->redirector->gotoUrl('/admin/trigger/details/?triggerId=' . $triggerId);
                
            } else {
                $messages[] = 'There was an error processing the form';
            }
        }
        
        $vars = array();
        foreach ($this->_triggerConfig->triggers->{$triggerId}->vars as $key => $value) {
            $vars[$key] = $value;
        }
        
        if (count($vars) != 0) {
            $this->view->javascript = array(
               'sortable.js',
            );
        }
        
        $this->view->messages = $messages;
        $this->view->templateVars = $vars;        
        $this->view->form = $form;
    }
    
    /**
     * delete an existing trigger action
     *
     */
    public function deleteAction()
    {
    	
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerActionId)) {
            throw new Ot_Exception_Input('Trigger Action ID not found in query string.');
        }
        
        $action = new Ot_Trigger_Action();
        
        $thisAction = $action->find($get->triggerActionId);
        
        if (is_null($thisAction)) {
            throw new Ot_Exception_Data('Trigger action does not exist.');
        }
            	
        $triggerId = $thisAction->triggerId;
        
        $form = new Zend_Form();
        $form->setAction('?triggerActionId=' . $get->triggerActionId)
             ->setMethod('post')
             ->setAttrib('id', 'deleteAction')
             ;
        
        $submit = $form->createElement('submit', 'deleteButton', array('label' => 'Delete Action'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));        
                             
        $form->addElements(array($submit, $cancel));     	
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
        	
        	$where = $action->getAdapter()->quoteInto('triggerActionId = ?', $get->triggerActionId);
        	
        	$action->delete($where);
        	
        	$obj = $thisAction->helper;
        	
        	$thisHelper = new $obj;
        	
        	$thisHelper->deleteProcess($get->triggerActionId);
        	
        	$this->_logger->setEventItem('attributeName', 'triggerActionId');
            $this->_logger->setEventItem('attributeId', $get->triggerActionId);
            $this->_logger->info('Trigger Action deleted');          
        
            $this->_flashMessenger->addMessage('The action was deleted successfully.');
            
            $this->_helper->redirector->gotoUrl('/admin/trigger/details/?triggerId=' . $triggerId);
        }
        
        $this->view->form = $form;
        $this->view->action = $thisAction->toArray();
        $this->view->triggerId = $triggerId;
        $this->view->title = 'Delete Action';
    }
}