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
 * @package    Ot_CustomController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the management of custom attributes to certain parent nodes within
 * the application.
 *
 * @package    Ot_CustomController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_CustomController extends Zend_Controller_Action
{
    /**
     * Shows all available nodes to add attributes to
     */
    public function indexAction()
    {
        $cahr = new Ot_CustomAttribute_HostRegister();

        $hosts = $cahr->getHosts();

        $this->_helper->pageTitle('ot-custom-index:title');

        $this->view->acl = array(
            'details' => $this->_helper->hasAccess('details')
        );

        $this->view->assign(array(
            'hosts' => $hosts,
        ));

    }

    /**
     * Shows all attributes associated with the selected node
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'            => $this->_helper->hasAccess('index'),
            'add'              => $this->_helper->hasAccess('add'),
            'edit'             => $this->_helper->hasAccess('edit'),
            'delete'           => $this->_helper->hasAccess('delete'),
            'attributeDetails' => $this->_helper->hasAccess('attributeDetails'),
        );
        
        $key = $this->_getParam('key', null);

        if (is_null($key)) {
            throw new Ot_Exception_Input('msg-error-objectNotFound');
        }

        $cahr = new Ot_CustomAttribute_HostRegister();

        $thisHost = $cahr->getHost($key);

        if (is_null($thisHost)) {
            throw new Ot_Exception_Data('msg-error-objectNotSetup');
        }
        
        $ftr = new Ot_CustomAttribute_FieldTypeRegister();
        
        $fieldTypes = $ftr->getFieldTypes();
        
        $this->_helper->pageTitle('ot-custom-details:title', $thisHost->getName());
        
        $this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js');
        
        $this->view->assign(array(
            'attributes' => $thisHost->getAttributes(),
            'host'       => $thisHost,
            'fieldTypes' => $fieldTypes,
        ));
    }


    /**
     * Adds a new attribute to a node
     *
     */
    public function addAction()
    {
        $key = $this->_getParam('key', null);
        $fieldTypeKey = $this->_getParam('fieldTypeKey', null);
        
        if (is_null($key)) {
            throw new Ot_Exception_Input('msg-error-objectNotFound');
        }

        $cahr = new Ot_CustomAttribute_HostRegister();

        $thisHost = $cahr->getHost($key);

        if (is_null($thisHost)) {
            throw new Ot_Exception_Input('msg-error-objectNotSetup');
        }

        $caft = new Ot_CustomAttribute_FieldTypeRegister();
        
        $thisFieldType = $caft->getFieldType($fieldTypeKey);
        
        if (is_null($thisFieldType)) {
            throw new Ot_Exception_Input('Field type not setup in bootstrap');
        }
        
        $numberOfOptions = ($this->_request->isPost()) ? $this->_getParam('rowCt', 0) : (($thisFieldType->hasOptions()) ? 1 : 0);
        
        $form = new Ot_Form_CustomAttribute(array('numberOfOptions' => $numberOfOptions));

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $data = array(
                    'hostKey'      => $key,
                    'fieldTypeKey' => $fieldTypeKey,
                    'label'        => $form->getValue('label'),
                    'description'  => $form->getValue('description'),
                    'required'     => $form->getValue('required'),
                );
                
                if ($thisFieldType->hasOptions()) {
                    $options = array();
                    foreach ($form->getValue('options') as $o) {
                        if ($o['option'] != '') {
                            $options[] = $o['option'];
                        }
                    }

                    $data['options'] = serialize($options);
                }
                
                $attr = new Ot_Model_DbTable_CustomAttribute();
                
                $attributeId = $attr->insert($data);
                
                $logOptions = array('attributeName' => 'hostKey', 'attributeId' => $data['hostKey']);

                $this->_helper->log(Zend_Log::INFO, 'Attribute ' . $data['label'] . ' added', $logOptions);

                $logOptions = array('attributeName' => 'attributeId', 'attributeId' => $attributeId);
                    
                $this->_helper->log(Zend_Log::INFO, $data['label'] . ' added', $logOptions);
            
                $this->_helper->messenger->addSuccess($this->view->translate('msg-info-attributeAdded', array($data['label'], $thisHost->getName())));

                $this->_helper->redirector->gotoRoute(array('controller' => 'custom', 'action' => 'details', 'key' => $key), 'ot', true);
            }
        }
        
        $this->_helper->pageTitle('ot-custom-add:title', array($thisFieldType->getName(), $thisHost->getName()));
        
        $this->view->assign(array(
            'fieldType' => $thisFieldType,
            'host'      => $thisHost,
            'form'      => $form,
        ));
    }

    /**
     * Modifies an existing attribute
     *
     */
    public function editAction()
    {
        $attr = new Ot_Model_DbTable_CustomAttribute();

        $attributeId = $this->_getParam('attributeId', null);

        if (is_null($attributeId)) {
            throw new Ot_Exception_Input('msg-error-attributeIdNotSet');
        }

        $thisAttribute = $attr->get($attributeId);       
                
        $numberOfOptions = ($this->_request->isPost()) ? $this->_getParam('rowCt', 0) : count($thisAttribute['options']);
        
        $form = new Ot_Form_CustomAttribute(array('numberOfOptions' => $numberOfOptions));
        $form->populate($thisAttribute);
                
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $data = array(
                    'attributeId' => $attributeId,
                    'label'       => $form->getValue('label'),
                    'description' => $form->getValue('description'),
                    'required'    => $form->getValue('required'),
                );
                
                $options = array();
                foreach ($form->getValue('options') as $o) {
                    if ($o['option'] != '') {
                        $options[] = $o['option'];
                    }
                }
                
                $data['options'] = serialize($options);
                
                $attr->update($data, null);

                $logOptions = array(
                    'attributeName' => 'nodeAddtributeId', 
                    'attributeId' => $data['attributeId']
                );

                $this->_helper->log(Zend_Log::INFO, 'Attribute ' . $data['label'] . ' was modified.', $logOptions);
                $this->_helper->messenger->addSuccess($this->view->translate('msg-info-attributeSaved', array($data['label'])));

                $this->_helper->redirector->gotoRoute(array('controller' => 'custom', 'action' => 'details', 'key' => $thisAttribute['hostKey']), 'ot', true);
            }                       
        }

        $this->_helper->pageTitle('ot-custom-edit:title', array($thisAttribute['fieldType']->getName(), $thisAttribute['host']->getName()));
        
        $this->view->assign(array(
            'form'      => $form,
            'attribute' => $thisAttribute,
        ));
    }

    /**
     * Deletes an attribute
     *
     */
    public function deleteAction()
    {
        $attr = new Ot_Model_DbTable_CustomAttribute();

        $attributeId = $this->_getParam('attributeId', null);

        if (is_null($attributeId)) {
            throw new Ot_Exception_Input('msg-error-attributeIdNotSet');
        }

        $thisAttribute = $attr->get($attributeId);                
        
        if ($this->_request->isPost()) {

            $where = $attr->getAdapter()->quoteInto('attributeId = ?', $attributeId);
            $attr->delete($where);

            $val = new Ot_Model_DbTable_CustomAttributeValue();
            $val->delete($where);

            $logOptions = array('attributeName' => 'objectAttributeId', 'attributeId' => $attributeId);
                    
            $this->_helper->log(Zend_Log::INFO, 'Attribute and all values were deleted', $logOptions);

            $this->_helper->messenger->addWarning($this->view->translate('msg-info-attributeDeleted', array($thisAttribute['label'])));

            $this->_helper->redirector->gotoRoute(array('controller' => 'custom', 'action' => 'details', 'key' => $thisAttribute['hostKey']), 'ot', true);
        } else {
            throw new Ot_Exception_Access('You can not access this method directly.');
        }
    }     
    

    /**
     * Updates the display order of the attributes from the AJAX request
     *
     */
    public function saveAttributeOrderAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $this->_helper->layout->disableLayout();

        $key = $this->_getParam('key', null);
        $attributeIds = $this->_getParam('attributeIds', array());
        
        if (is_null($key)) {
            $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-objectIdNotSet'));
            echo Zend_Json_Encoder::encode($ret);
            return;
        }

        $cahr = new Ot_CustomAttribute_HostRegister();

        $thisHost = $cahr->getHost($key);

        if (is_null($thisHost)) {
            $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-objectIdNotSet'));
            echo Zend_Json_Encoder::encode($ret);
            return;
        }
        
        if (count($attributeIds) == 0) {
            $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-attributeIdsNotSet'));
            echo Zend_Json_Encoder::encode($ret);
            return;
        }

        if ($this->_request->isPost()) {
            
            $attr = new Ot_Model_DbTable_CustomAttribute();
        
            $dba = $attr->getAdapter();

            $dba->beginTransaction();
                        
            $i = 1;
            foreach ($attributeIds as $id) {
                $id = (int)substr($id, strpos($id, '_') + 1);
                
                $data = array("order" => $i);

                $where = $dba->quoteInto('attributeId = ?', $id) .
                         " AND " .
                         $dba->quoteInto('hostKey = ?', $key);

                try {
                    $attr->update($data, $where);
                } catch(Exception $e) {
                    $dba->rollBack();
                    $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-orderNotSaved', $e->getMessage()));
                    echo Zend_Json_Encoder::encode($ret);
                    return;
                }
                $i++;
            }

            $dba->commit();

            $logOptions = array('attributeName' => 'hostKey', 'attributeId' => $key);
                    
            $this->_helper->log(Zend_Log::INFO, $thisHost->getName() . ' had attributes reordered', $logOptions);
            
            $ret = array('rc' => 1, 'msg' => $this->view->translate('msg-info-newOrderSaved'));
            echo Zend_Json_Encoder::encode($ret);
            return;
        }
    }    
}