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
            'copy'         => $this->_helper->hasAccess('copy'),
            'edit'         => $this->_helper->hasAccess('edit'),
            'delete'       => $this->_helper->hasAccess('delete'),
            'changeStatus' => $this->_helper->hasAccess('change-status'),
        );
        
        $eventKey = $this->_getParam('eventKey', null);
       
        if (is_null($eventKey)) {
            throw new Ot_Exception_Input('msg-error-triggerIdNotFound');
        }

        $register = new Ot_Trigger_EventRegister();

        $thisTriggerEvent = $register->getTriggerEvent($eventKey);
        
        if (is_null($thisTriggerEvent)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }        
        
        $action = new Ot_Model_DbTable_TriggerAction();

        $where = $action->getAdapter()->quoteInto('eventKey = ?', $thisTriggerEvent->getKey());
        $actions = $action->fetchAll($where)->toArray();
        
        $tpr = new Ot_Trigger_ActionTypeRegister();
        $actionTypes = $tpr->getTriggerActionTypes();
        
        if (count($actionTypes) == 0) {
            throw new Ot_Exception_Data('model-trigger-action:noHelpersDefined');
        }
        
        $actionsWithActionTypes = array();
        
        foreach ($actions as $a) {
            if (isset($actionTypes[$a['actionKey']])) {
                $a['actionType'] = $actionTypes[$a['actionKey']];
            }
            
            $actionsWithActionTypes[] = $a;
        }
        
        $this->_helper->pageTitle('ot-trigger-details:title', $thisTriggerEvent->getName());
        
        $this->view->assign(array(
            'actions'      => $actionsWithActionTypes,
            'actionTypes'  => $actionTypes,
            'triggerEvent' => $thisTriggerEvent,
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

        $thisTriggerEvent = $register->getTriggerEvent($eventKey);

        if (is_null($thisTriggerEvent)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }        
        
        $actionTypeRegister = new Ot_Trigger_ActionTypeRegister();
        
        $thisActionType = $actionTypeRegister->getTriggerActionType($actionKey);
        
        if (is_null($thisActionType)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $form = new Ot_Form_TriggerAction($thisActionType->getForm());
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                                
                $data = array(
                    'eventKey'  => $thisTriggerEvent->getKey(),
                    'actionKey' => $thisActionType->getKey(),
                    'name'      => $form->getValue('name'),
                );

                $dba = $action->getAdapter();
                
                $dba->beginTransaction();
                
                try {
                    $triggerActionId = $action->insert($data);
                    
                    $actionTypeFormData = $form->getSubForm('actionType')->getValues();
                    
                    $actionTypeData = $actionTypeFormData['actionType'];
                    
                    $actionTypeData['triggerActionId'] = $triggerActionId;
            
                    $thisActionType->getDbTable()->insert($actionTypeData);
                    
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
                
                $dba->commit();
                
                $logOptions = array('attributeName' => 'triggerActionId', 'attributeId'   => $triggerActionId);
                
                $this->_helper->log(Zend_Log::INFO, 'Trigger Action added', $logOptions);
                $this->_helper->messenger->addSuccess('msg-info-triggerAdded');                             
                    
                $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'details', 'eventKey' => $thisTriggerEvent->getKey()), 'ot', true);
                    
            } else {
                $this->_helper->messenger->addError('msg-error-formError');
            }
        }
           
        $this->_helper->pageTitle('ot-trigger-add:title', array($thisActionType->getName(), $thisTriggerEvent->getName()));
                   
        $this->view->assign(array(
            'triggerEvent' => $thisTriggerEvent,
            'form'         => $form,
        ));
        
    }

    /**
     * Edit an existing action of a trigger
     *
     */
    public function editAction()
    {       
        $triggerActionId = $this->_getParam('triggerActionId', null);
        
        if (is_null($triggerActionId)) {
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisTriggerAction = $action->find($triggerActionId);
        
        if (is_null($thisTriggerAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }
        
        $register = new Ot_Trigger_EventRegister();

        $thisTriggerEvent = $register->getTriggerEvent($thisTriggerAction->eventKey);

        if (is_null($thisTriggerEvent)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }        
        
        $actionTypeRegister = new Ot_Trigger_ActionTypeRegister();
        
        $thisActionType = $actionTypeRegister->getTriggerActionType($thisTriggerAction->actionKey);
        
        if (is_null($thisActionType)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }
                
        $actionTypeForm = $thisActionType->getForm();
        
        $actionTypeFormData = $thisActionType->getDbTable()->find($triggerActionId);
        if (is_null($actionTypeFormData)) {
            throw new Ot_Exception_Data('Data not found for this trigger action');
        }
        
        $actionTypeForm->populate($actionTypeFormData->toArray());
        
        $form = new Ot_Form_TriggerAction($actionTypeForm);
        $form->populate($thisTriggerAction->toArray());        
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $data = array(
                    'triggerActionId' => $triggerActionId,
                    'name'            => $form->getValue('name'),
                );
                
                $dba = $action->getAdapter();
                
                $dba->beginTransaction();
                
                try {
                    $action->update($data, null);
                    
                    $actionTypeFormData = $form->getSubForm('actionType')->getValues();
                    
                    $actionTypeData = $actionTypeFormData['actionType'];
                    
                    $actionTypeData['triggerActionId'] = $triggerActionId;
                    
                    $thisActionType->getDbTable()->update($actionTypeData, null);
                    
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
                
                $dba->commit();
                
                $logOptions = array('attributeName' => 'triggerActionId', 'attributeId' => $triggerActionId);
                    
                $this->_helper->log(Zend_Log::INFO, 'Trigger Action modified', $logOptions);
            
                $this->_helper->messenger->addSuccess('msg-info-triggerUpdated');
                
                $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'details', 'eventKey' => $thisTriggerEvent->getKey()),'ot', true);
                
            } else {
                $this->_helper->messenger->addError('msg-error-formError');
            }
        }
            
        $this->_helper->pageTitle('ot-trigger-edit:title', array($thisTriggerAction->name, $thisActionType->getName(), $thisTriggerEvent->getName() ));
        
        $this->view->assign(array(
            'form'         => $form,
            'triggerEvent' => $thisTriggerEvent,
        ));
    }
    
    public function copyAction()
    {
        $triggerActionId = $this->_getParam('triggerActionId', null);
        
        if (is_null($triggerActionId)) {
            throw new Ot_Exception_Input('msg-error-triggerActionIdNotFound');
        }
        
        $action = new Ot_Model_DbTable_TriggerAction();
        
        $thisTriggerAction = $action->find($triggerActionId);
        
        if (is_null($thisTriggerAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }
                
        $register = new Ot_Trigger_EventRegister();

        $thisTriggerEvent = $register->getTriggerEvent($thisTriggerAction->eventKey);

        if (is_null($thisTriggerEvent)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }        
        
        $actionTypeRegister = new Ot_Trigger_ActionTypeRegister();
        
        $thisActionType = $actionTypeRegister->getTriggerActionType($thisTriggerAction->actionKey);
        
        if (is_null($thisActionType)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }
                        
        $actionTypeData = $thisActionType->getDbTable()->find($triggerActionId);
        
        if (is_null($actionTypeData)) {
            throw new Ot_Exception_Data('Data not found for this trigger action');
        }
                
        if ($this->_request->isPost()) {
                
            $dba = $action->getAdapter();
                
            $dba->beginTransaction();

            $data = array(
                'name'      => 'Copy of ' . $thisTriggerAction->name,
                'actionKey' => $thisTriggerAction->actionKey,
                'eventKey'  => $thisTriggerAction->eventKey,                   
            );
            
            try {
                $triggerActionId = $action->insert($data);
                
                $actionTypeData = $actionTypeData->toArray();
                
                $actionTypeData['triggerActionId'] = $triggerActionId;

                $thisActionType->getDbTable()->insert($actionTypeData);

            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            $dba->commit();

            $logOptions = array('attributeName' => 'triggerActionId', 'attributeId'   => $triggerActionId);
           
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action cloned', $logOptions);
            $this->_helper->messenger->addSuccess($this->view->translate('msg-info-triggerCloned', array('clonedTriggerName' => $data['name'])));             
            
            $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'edit', 'triggerActionId' => $triggerActionId),'ot', true);
            
        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }        
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
        
        $thisTriggerAction = $action->find($triggerActionId);
        
        if (is_null($thisTriggerAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }                
        
        $actionTypeRegister = new Ot_Trigger_ActionTypeRegister();
        
        $thisActionType = $actionTypeRegister->getTriggerActionType($thisTriggerAction->actionKey);
        
        if (is_null($thisActionType)) {
            throw new Ot_Exception_Data('msg-error-noTrigger');
        }
        
        if ($this->_request->isPost()) {
                
            $dba = $action->getAdapter();
            
            $dba->beginTransaction();
                        
            $where = $action->getAdapter()->quoteInto('triggerActionId = ?', $triggerActionId);
            
            try {
                
                $action->delete($where);
                
                $thisActionType->getDbTable()->delete($where);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }
            
            $dba->commit();
                
            $logOptions = array(
                'attributeName' => 'triggerActionId', 
                'attributeId'   => $triggerActionId
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->messenger->addWarning('msg-info-triggerDeleted');
            
            $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'details', 'eventKey' => $thisTriggerAction->eventKey), 'ot', true);
            
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
        
        $thisTriggerAction = $action->find($triggerActionId);
        
        if (is_null($thisTriggerAction)) {
            throw new Ot_Exception_Data('msg-error-noTriggerActionId');
        }                
        
        if ($this->_request->isPost()) {

            $data = array(
                'triggerActionId' => $triggerActionId, 
                'enabled'         => !$thisTriggerAction->enabled
            );
            
            $action->update($data, null);
            
            $logOptions = array('attributeName' => 'triggerActionId', 'attributeId' => $triggerActionId);
                    
            $this->_helper->log(Zend_Log::INFO, 'Trigger Action deleted', $logOptions);
        
            $this->_helper->messenger->addWarning('msg-info-triggerActionStatus');
            
            $this->_helper->redirector->gotoRoute(array('controller' => 'trigger', 'action' => 'details', 'eventKey' => $thisTriggerAction->eventKey), 'ot', true);
        } else {
            throw new Ot_Exception_Access('You are not allowed to access this method directly');
        }
    }
}