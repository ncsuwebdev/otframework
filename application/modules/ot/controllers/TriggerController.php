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
 * @package    Ot_TriggerController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University
 *             Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manages the triggers that are dispatched from the application based on an
 * event.
 *
 * @package    Ot_TriggerController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_TriggerController extends Zend_Controller_Action
{        
    /**
     * Shows all availabe triggers
     */
    public function indexAction()
    {
        $this->_helper->pageTitle('ot-trigger-index:title');
        
        $this->view->acl = array('details' => $this->_helper->hasAccess('details'));

        $register = new Ot_Trigger_Register();

        $this->view->triggers = $register->getTriggers();
    }
    
    /**
     * Shows the actions for the selected trigger
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'        => $this->_helper->hasAccess('index'),
            'add'          => $this->_helper->hasAccess('add'),
            'edit'         => $this->_helper->hasAccess('edit'),
            'delete'       => $this->_helper->hasAccess('delete'),
            'changeStatus' => $this->_helper->hasAccess('change-status'),
        );
        
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->name)) {
            throw new Ot_Exception_Input('msg-error-triggerIdNotFound');
        }

        $register = new Ot_Trigger_Register();

        $thisTrigger = $register->getTrigger($get->name);
        
        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }
        
        $this->view->trigger = $thisTrigger;
        $this->_helper->pageTitle('ot-trigger-details:title', $thisTrigger->getName());
        
        $action = new Ot_Model_DbTable_TriggerAction();

        $where = $action->getAdapter()->quoteInto('triggerId = ?', $thisTrigger->getName());
        $actions = $action->fetchAll($where)->toArray();
        
        $tpr = new Ot_Trigger_PluginRegister();
        
        foreach ($actions as &$a) {
            $a['helper'] = $tpr->getTriggerPlugin($a['helper']);
        }
        
        $this->view->actions = $actions;
        
        $this->view->messages = $this->_helper->messenger->getMessages();
    }
    
    /**
     * Add a new action to the trigger
     *
     */
    public function addAction()
    {
    	// TODO: refactor this because it has confusing variable names (name/triggerId are the same thing)
        $get = Zend_Registry::get('getFilter');
        
        $action = new Ot_Model_DbTable_TriggerAction();

        $register = new Ot_Trigger_Register();

        $thisTrigger = $register->getTrigger($get->triggerId);

        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }

        $this->view->trigger = $thisTrigger;
        $this->_helper->pageTitle('ot-trigger-add:title');
        
        $values = array('triggerId' => $thisTrigger->getName());
        
        if (isset($get->helper)) {
            $values['helper'] = $get->helper;
        }
        
        if (isset($get->triggerActionId) && $get->triggerActionId != '') {

            $actionToClone = $action->find($get->triggerActionId);
            
            if (!is_null($actionToClone)) {

                $values = array_merge($values, $actionToClone->toArray());

                $this->_helper->pageTitle('ot-trigger-add:cloneTitle', array('triggerName' => $values['name']));
                
                $clonedTriggerName = $values['name'];
                if (strpos($values['name'], 'clone-') === false) {
                   $values['name'] = 'clone-' . $values['name'];
                }
            }            
        }
        
        $form = $action->form($values);
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $data = array(
                    'triggerId' => $thisTrigger->getName(),
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
                    
                $logOptions = array('attributeName' => 'triggerActionId', 'attributeId'   => $triggerActionId);
                if (!$clonedTriggerName) {
                    $this->_helper->log(Zend_Log::INFO, 'Trigger Action added', $logOptions);
                    $this->_helper->messenger->addSuccess('msg-info-triggerAdded');
                } else {
                    $this->_helper->log(Zend_Log::INFO, 'Trigger Action cloned', $logOptions);
                    $this->_helper->messenger->addSuccess($this->view->translate('msg-info-triggerCloned', array('clonedTriggerName' => $data['name'])));
                }
                    
                $this->_helper->redirector->gotoRoute(
                    array(
                        'controller' => 'trigger',
                        'action'     => 'details',
                        'name'       => $thisTrigger->getName(),
                    ),
                    'ot',
                    true
                );
                    
            } else {
                $this->_helper->messenger->addError('msg-error-formError');
            }
        }
        
        if (isset($clonedTriggerName) && $clonedTriggerName) {
            $this->view->clonedTriggerName = $clonedTriggerName;
        }
        
        $this->view->form         = $form;
    }

    /**
     * Edit an existing action of a trigger
     *
     */
    public function editAction()
    {       
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerActionId)) {
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisAction = $action->find($get->triggerActionId);
        
        if (is_null($thisAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }
        
        $register = new Ot_Trigger_Register();
        $thisTrigger = $register->getTrigger($thisAction->triggerId);

        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }

        $this->view->trigger = $thisTrigger;
       
        
        $this->_helper->pageTitle('ot-trigger-edit:title');

        $tpr = new Ot_Trigger_PluginRegister();

        $thisPlugin = $tpr->getTriggerPlugin($thisAction->helper);

        if (is_null($thisPlugin)) {
            throw new Ot_Exception_Data('msg-error-triggerHelperNotFound');
        }
        
        $values = array('triggerActionId' => $get->triggerActionId);
        $values = array_merge($values, $thisAction->toArray());
        
        $form = $action->form($values);
        
        
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
                
                $logOptions = array('attributeName' => 'triggerActionId', 'attributeId'   => $get->triggerActionId);
                    
                $this->_helper->log(Zend_Log::INFO, 'Trigger Action modified', $logOptions);
            
                $this->_helper->messenger->addSuccess('msg-info-triggerUpdated');
                
                $this->_helper->redirector->gotoRoute(
                    array(
                        'controller' => 'trigger', 'action' => 'details', 'name' => $thisTrigger->getName()),
                    'ot',
                    true
                );
                
            } else {
                $this->_helper->messenger->addError('msg-error-formError');
            }
        }
            
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
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisAction = $action->find($get->triggerActionId);
        
        if (is_null($thisAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }
                    
        $triggerId = $thisAction->triggerId;
        
        $form = Ot_Form_Template::delete('deleteTrigger');             
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
                
            $where = $action->getAdapter()->quoteInto('triggerActionId = ?', $get->triggerActionId);
                
            $action->delete($where);
                
            $obj = $thisAction->helper;
                
            $thisHelper = new $obj;
                
            $thisHelper->deleteProcess($get->triggerActionId);
                
            $logOptions = array('attributeName' => 'triggerActionId', 'attributeId'   => $get->triggerActionId);
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->messenger->addWarning('msg-info-triggerDeleted');
            
            $this->_helper->redirector->gotoRoute(
                array('controller' => 'trigger', 'action' => 'details', 'name' => $triggerId),
                'ot',
                true
            );
        }
        
        $this->view->form = $form;
        $this->view->action = $thisAction->toArray();
        $this->view->triggerId = $triggerId;
        $this->_helper->pageTitle('ot-trigger-delete:title');
    }
    
    /**
     * Allows users to enable/disable trigger action
     *
     */
    public function changeStatusAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->triggerActionId)) {
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisAction = $action->find($get->triggerActionId);
        
        if (is_null($thisAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }
        
        $triggerId  = $thisAction->triggerId;
        $buttonText = 'form-button-enable';
        $status     = 'enable';
        
        if ($thisAction->enabled == 1) {
            $buttonText = 'form-button-disable';
            $status     = 'disable';
        }
        
        $this->view->status = $status;
        
        $form = Ot_Form_Template::delete('changeStatus', $buttonText);             
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            $data = array('triggerActionId' => $get->triggerActionId, 'enabled' => !$thisAction->enabled);
            
            $action->update($data, null);
            
            $logOptions = array('attributeName' => 'triggerActionId', 'attributeId' => $get->triggerActionId);
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->messenger->addWarning('msg-info-triggerActionStatus');
            
            $this->_helper->redirector->gotoRoute(
                array('controller' => 'trigger', 'action' => 'details', 'name' => $triggerId),
                'ot',
                true
            );
        }
        
        $this->view->form = $form;
        $this->view->action = $thisAction->toArray();
        $this->view->triggerId = $triggerId;
        $this->_helper->pageTitle('ot-trigger-changeStatus:title', array(ucwords($status)));
    }
}