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
        
        $this->view->acl = array(
            'details' => $this->_helper->hasAccess('details')
        );

        $register = new Ot_Trigger_EventRegister();

        $this->view->triggerEvents = $register->getTriggerEvents();
    }
    
    /**
     * Shows the actions for the selected trigger
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'add'          => $this->_helper->hasAccess('add'),
            'edit'         => $this->_helper->hasAccess('edit'),
            'delete'       => $this->_helper->hasAccess('delete'),
            'changeStatus' => $this->_helper->hasAccess('change-status'),
        );
        
        $eventKey = $this->_getParam('eventKey', null);
       
        if (is_null($eventKey)) {
            throw new Ot_Exception_Input('msg-error-triggerIdNotFound');
        }

        $register = new Ot_Trigger_EventRegister();

        $thisTrigger = $register->getTriggerEvent($eventKey);
        
        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }        
        
        $action = new Ot_Model_DbTable_TriggerAction();

        $where = $action->getAdapter()->quoteInto('triggerId = ?', $thisTrigger->getKey());
        $actions = $action->fetchAll($where)->toArray();
        
        $tpr = new Ot_Trigger_ActionTypeRegister();
        $actionTypes = $tpr->getTriggerActionTypes();
        
        if (count($actionTypes) == 0) {
            throw new Ot_Exception_Data('model-trigger-action:noHelpersDefined');
        }
        
        $actionsWithHelpers = array();
        
        foreach ($actions as $a) {
            if (isset($actionTypes[$a['helper']])) {
                $a['helper'] = $actionTypes[$a['helper']];
            }
            
            $actionsWithHelpers[] = $a;
        }
        
        $this->_helper->pageTitle('ot-trigger-details:title', $thisTrigger->getName());
        
        $this->view->assign(array(
            'actions'     => $actionsWithHelpers,
            'actionTypes' => $actionTypes,
            'trigger'     => $thisTrigger,
            'messages'    => $this->_helper->messenger->getMessages(),
        ));        
    }
    
    /**
     * Add a new action to the trigger
     *
     */
    public function addAction()
    {
        $eventKey = $this->_getParam('eventKey', null);
        $actionKey = $this->_getParam('actionKey', null);
       
        if (is_null($eventKey)) {
            throw new Ot_Exception_Input('msg-error-triggerIdNotFound');
        }
        
        if (is_null($actionKey)) {
            throw new Ot_Exception_Input('msg-error-triggerIdNotFound');
        }
        
        $register = new Ot_Trigger_EventRegister();

        $thisTrigger = $register->getTriggerEvent($eventKey);

        if (is_null($thisTrigger)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }        
        
        $actionTypeRegister = new Ot_Trigger_ActionTypeRegister();
        
        $thisActionType = $actionTypeRegister->getTriggerActionType($actionKey);
        
        if (is_null($thisActionType)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        /*
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
        }*/
        
        $form = new Ot_Form_TriggerAction($thisActionType->addSubForm());
        
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
        $this->view->trigger = $thisTrigger;
        $this->_helper->pageTitle('ot-trigger-add:title');
        
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
        $triggerActionId = $this->_getParam('triggerActionId', null);
        
        if (is_null($triggerActionId)) {
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisAction = $action->find($triggerActionId);
        
        if (is_null($thisAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }                
        
        if ($this->_request->isPost()) {
                
            $where = $action->getAdapter()->quoteInto('triggerActionId = ?', $triggerActionId);
                
            $action->delete($where);
                
            $obj = $thisAction->helper;
                
            $thisHelper = new $obj;
                
            $thisHelper->deleteProcess($triggerActionId);
                
            $logOptions = array(
                'attributeName' => 'triggerActionId', 
                'attributeId'   => $triggerActionId
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->messenger->addWarning('msg-info-triggerDeleted');
            
            $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'details', 'eventKey' => $thisAction->triggerId), 'ot', true);
            
        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }
    }
    
    /**
     * Allows users to enable/disable trigger action
     *
     */
    public function changeStatusAction()
    {
        $triggerActionId = $this->_getParam('triggerActionId', null);
        
        if (is_null($triggerActionId)) {
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisAction = $action->find($triggerActionId);
        
        if (is_null($thisAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }                
        
        if ($this->_request->isPost()) {

            $data = array(
                'triggerActionId' => $triggerActionId, 
                'enabled'         => !$thisAction->enabled
            );
            
            $action->update($data, null);
            
            $logOptions = array('attributeName' => 'triggerActionId', 'attributeId' => $triggerActionId);
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->messenger->addWarning('msg-info-triggerActionStatus');
            
            $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'details', 'eventKey' => $thisAction->triggerId), 'ot', true);
        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }
    }
}