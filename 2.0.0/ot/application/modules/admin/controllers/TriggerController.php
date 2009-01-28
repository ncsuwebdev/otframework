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
class Admin_TriggerController extends Zend_Controller_Action  
{	
	/**
	 * Trigger config file
	 *
	 * @var Zend_Config
	 */
	protected $_triggerConfig = '';
	
	/**
	 * Setup controller vars
	 *
	 */
	public function init()
	{
		$config = Zend_Registry::get('config');
        $this->_triggerConfig = $config->triggers->trigger;
        
        parent::init();
	}
	
    /**
     * Shows all availabe triggers
     */
    public function indexAction()
    {
        $this->_helper->pageTitle('Trigger Index');
        
        $this->view->acl = array(
            'details'   => $this->_helper->hasAccess('details')
            );
        
        $this->view->triggers = $this->_triggerConfig;
    }
    
    /**
     * Shows the actions for the selected trigger
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'  => $this->_helper->hasAccess('index'),
            'add'    => $this->_helper->hasAccess('add'),
            'edit'   => $this->_helper->hasAccess('edit'),
            'delete' => $this->_helper->hasAccess('delete')
            );        
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerId)) {
        	throw new Ot_Exception_Input('Trigger ID not found in query string.');
        }
        
        $thisTrigger = null;
        foreach ($this->_triggerConfig as $t) {
            if ($t->name == $get->triggerId) {
                $thisTrigger = $t;
                break;
            }
        }
        
        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('Trigger does not exist in configuration file.');
        }
        
        $this->view->triggerId = $get->triggerId;
        $this->view->trigger = $thisTrigger;
        $this->_helper->pageTitle('Trigger Actions for ' . $get->triggerId);
        
        $action = new Ot_Trigger_Action();

        $where = $action->getAdapter()->quoteInto('triggerId = ?', $get->triggerId);
        $actions = $action->fetchAll($where)->toArray();
        
        $config = Zend_Registry::get('config');
        
        foreach ($actions as &$a) {
        	$a['helper'] = $config->app->triggerPlugins->{$a['helper']};
        }
        
        $this->view->actions = $actions;
        
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
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
        
        $thisTrigger = null;
        foreach ($this->_triggerConfig as $t) {
            if ($t->name == $get->triggerId) {
                $thisTrigger = $t;
                break;
            }
        }
        
        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('Trigger does not exist in configuration file.');
        }
        
        $this->view->triggerId = $get->triggerId;
        $this->view->trigger = $thisTrigger;
        $this->_helper->pageTitle('Select Trigger Type');
        
        $action = new Ot_Trigger_Action();

        $values = array('triggerId' => $get->triggerId);
        
        if (isset($get->helper)) {
            $values['helper'] = $get->helper;
        }
        
        $form = $action->form($values);
             
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
        		
        	    $subForm = $form->getSubForm($form->getValue('helper'));
                
                $elements = $subForm->getElements();
                
                $subData = array();
                foreach ($elements as $key => $value) {
                    $subData[$key] = $subForm->getValue($key);
                }
                $subData['triggerActionId'] = $triggerActionId;                
        		
                $obj = $form->getValue('helper');
                $thisHelper = new $obj;
                
        		$thisHelper->addProcess($subData);
        		
        		$logOptions = array(
                        'attributeName' => 'triggerActionId',
                        'attributeId'   => $triggerActionId,
                    );
                    
                $this->_helper->log(Zend_Log::INFO, 'Trigger Action added', $logOptions);
        		
        		$this->_helper->flashMessenger->addMessage('The action was added successfully.');
        		
        		$this->_helper->redirector->gotoUrl('/admin/trigger/details/?triggerId=' . $get->triggerId);
        		
        	} else {
        		$messages[] = 'There was an error processing the form';
        	}
        }
        
        $vars = array();
               
        foreach ($thisTrigger->var as $var) {
            $vars[$var->name] = $var->description;
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
         
        $thisTrigger = null;
        foreach ($this->_triggerConfig as $t) {
            if ($t->name == $triggerId) {
                $thisTrigger = $t;
                break;
            }
        }
        
        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('Trigger does not exist in configuration file.');
        }
       
        $this->view->trigger = $thisTrigger;
        
        $this->_helper->pageTitle('Select Trigger Type');

        $config = Zend_Registry::get('config');
        
        if (!isset($config->app->triggerPlugins->{$thisAction->helper})) {
            throw new Ot_Exception_Data('Trigger Helper not found');
        }
        
        $values = array('triggerActionId' => $get->triggerActionId);
        $values = array_merge($values, $thisAction->toArray());
        
        $form = $action->form($values);      
             
        $messages = array();
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $data = array(
                  'triggerActionId' => $form->getValue('triggerActionId'),
                  'name'            => $form->getValue('name'),
                );
                
                $action->update($data, null);

                $subForm = $form->getSubForm($form->getValue('helper'));
                
                $elements = $subForm->getElements();
                
                $subData = array();
                foreach ($elements as $key => $value) {
                    $subData[$key] = $subForm->getValue($key);
                }
                $subData['triggerActionId'] = $form->getValue('triggerActionId');
                
                $obj = $form->getValue('helper');
                $thisHelper = new $obj;               
                
                $thisHelper->editProcess($subData);
                
                $logOptions = array(
                       'attributeName' => 'triggerActionId',
                       'attributeId'   => $get->triggerActionId,
                );
                    
                $this->_helper->log(Zend_Log::INFO, 'Trigger Action modified', $logOptions);
            
                $this->_helper->flashMessenger->addMessage('The action was modified successfully.');
                
                $this->_helper->redirector->gotoUrl('/admin/trigger/details/?triggerId=' . $triggerId);
                
            } else {
                $messages[] = 'There was an error processing the form';
            }
        }
        
        $vars = array();
        foreach ($thisTrigger->var as $var) {
            $vars[$var->name] = $var->description;
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
        
        $form = Ot_Form_Template::delete('deleteTrigger');     	
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
        	
        	$where = $action->getAdapter()->quoteInto('triggerActionId = ?', $get->triggerActionId);
        	
        	$action->delete($where);
        	
        	$obj = $thisAction->helper;
        	
        	$thisHelper = new $obj;
        	
        	$thisHelper->deleteProcess($get->triggerActionId);
        	
        	$logOptions = array(
                       'attributeName' => 'triggerActionId',
                       'attributeId'   => $get->triggerActionId,
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->flashMessenger->addMessage('The action was deleted successfully.');
            
            $this->_helper->redirector->gotoUrl('/admin/trigger/details/?triggerId=' . $triggerId);
        }
        
        $this->view->form = $form;
        $this->view->action = $thisAction->toArray();
        $this->view->triggerId = $triggerId;
        $this->_helper->pageTitle('Delete Action');
    }
}