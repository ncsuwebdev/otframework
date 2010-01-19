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
            $config = Zend_Registry::get('config');

        $this->_helper->pageTitle('ot-custom-index:title');

        $this->view->acl = array(
            'details'   => $this->_helper->hasAccess('details')
            );

        $objects = array();
        foreach ($config->app->customFieldObjects as $key => $value) {

            $objects[] = array(
               'objectId' => $key,
               'description' => $value,
            );
        }

        $this->view->objects = $objects;

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
            'attributeDetails' => $this->_helper->hasAccess('attributeDetails')
            );

        $get = Zend_Registry::get('getFilter');
        if (!isset($get->objectId)) {
                throw new Ot_Exception_Input('msg-error-objectNotFound');
        }

        $config = Zend_Registry::get('config');

        if (!isset($config->app->customFieldObjects->{$get->objectId})) {
                throw new Ot_Exception_Input('msg-error-objectNotSetup');
        }

        $custom = new Ot_Custom();
        $attributes = $custom->getAttributesForObject($get->objectId);

        $this->_helper->pageTitle('ot-custom-details:title', $get->objectId);
        
        $this->view->attributes        = $attributes;
        $this->view->objectId          = $get->objectId;
        $this->view->objectDescription = $config->app->customFieldObjects->{$get->objectId};
        $this->view->messages          = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * Updates the display order of the attributes from the AJAX request
     *
     */
    public function saveAttributeOrderAction()
    {
            $this->_helper->viewRenderer->setNeverRender();
            $this->_helper->layout->disableLayout();

        if ($this->_request->isPost()) {
            
            $post = Zend_Registry::get('postFilter');
            
            if (!isset($post->objectId)) {
                $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-objectIdNotSet'));
                echo Zend_Json_Encoder::encode($ret);
                return;
            }
            
            if (!isset($post->attributeIds)) {
                $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-attributeIdsNotSet'));
                echo Zend_Json_Encoder::encode($ret);
                return;
            }

            $objectId = $post->objectId;
            $attributeIds = $post->attributeIds;
            
            foreach ($attributeIds as &$id) {
                $id = (int)substr($id, strpos($id, '_')+1);
            }

            $custom = new Ot_Custom();

            try {
                $custom->updateAttributeOrder($objectId, $attributeIds);
                $ret = array('rc' => 1, 'msg' => $this->view->translate('msg-info-newOrderSaved'));
                echo Zend_Json_Encoder::encode($ret);
                return;
            } catch (Exception $e) {
                    $ret = array('rc' => 0, 'msg' => $this->view->translate('msg-error-orderNotSaved', $e->getMessage()));
                echo Zend_Json_Encoder::encode($ret);
                return;
            }

            $logOptions = array(
                        'attributeName' => 'objectId',
                        'attributeId'   => $objectId,
            );
                    
            $this->_helper->log(Zend_Log::INFO, $objectId . ' had attributes reordered', $logOptions);
        }
    }

    /**
     * Shows the details of a selected attribute
     *
     */
    public function attributeDetailsAction()
    {
        $this->view->acl = array(
            'add'    => $this->_helper->hasAccess('add'),
            'edit'   => $this->_helper->hasAccess('edit'),
            'delete' => $this->_helper->hasAccess('delete')
            );

            $get = Zend_Registry::get('getFilter');

            $custom = new Ot_Custom();
            $attr   = new Ot_Custom_Attribute();

            if (!isset($get->attributeId)) {
                    throw new Ot_Exception_Input('msg-error-attributeIdNotSet');
            }

            $attribute = $attr->find($get->attributeId);

            if (is_null($attribute)) {
                    throw new Ot_Exception_Data('msg-error-noAttribute');
            }

            $attribute = $attribute->toArray();

            $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);

        $config = Zend_Registry::get('config');

        if (!isset($config->app->customFieldObjects->{$attribute['objectId']})) {
            throw new Ot_Exception_Input('msg-error-objectNotSetup');
        }

            $this->view->attribute = $attribute;
        $this->view->objectId = $attribute['objectId'];
        $this->view->objectDescription = $config->app->customFieldObjects->{$attribute['objectId']};
            $this->_helper->pageTitle('ot-custom-attributeDetails:title');
    }

    /**
     * Adds a new attribute to a node
     *
     */
    public function addAction()
    {
        $get = Zend_Registry::get('getFilter');
        if (!isset($get->objectId)) {
            throw new Ot_Exception_Input('msg-error-objectNotFound');
        }

        $config = Zend_Registry::get('config');

        if (!isset($config->app->customFieldObjects->{$get->objectId})) {
            throw new Ot_Exception_Input('msg-error-objectNotSetup');
        }

        $custom = new Ot_Custom();

        if ($this->_request->isPost()) {

            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim())
                   ->addfilter(new Zend_Filter_HtmlEntities());

            $options = array();
            if (isset($_POST['option'])) {
                    foreach ($_POST['option'] as $o) {
                        if ($o != '') {
                        $options[] = $filter->filter($o);
                    }
                    }
            }

            $data = array(
                'objectId'    => $get->objectId,
                'label'     => $filter->filter($_POST['label']),
                'type'      => $filter->filter($_POST['type']),
                'options'   => $custom->convertOptionsToString($options),
                'required'  => (isset($_POST['required']) ? $filter->filter($_POST['required']) : 0),
                'direction' => $filter->filter($_POST['direction']),
                'order'     => 0,
                );

            $attr = new Ot_Custom_Attribute();

            $id = $attr->insert($data);

            $logOptions = array(
                        'attributeName' => 'objectId',
                        'attributeId'   => $data['objectId'],
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Attribute ' . $data['label'] . ' added', $logOptions);
            
            $logOptions = array(
                        'attributeName' => 'attributeId',
                        'attributeId'   => $id,
            );
                    
            $this->_helper->log(Zend_Log::INFO, $data['label'] . ' added', $logOptions);
            
            $this->_helper->flashMessenger->addMessage($this->view->translate('msg-info-attributeAdded', array($data['label'], $data['objectId'])));

            $this->_helper->redirector->gotoRoute(array('controller' => 'custom', 'action' => 'details', 'objectId' => $data['objectId']), 'ot', true);
        }

        $this->view->types = $custom->getTypes();
            $this->_helper->pageTitle('ot-custom-add:title', $get->objectId);
            $this->view->objectId = $get->objectId;
                        
        $this->view->objectDescription = $config->app->customFieldObjects->{$get->objectId};
    }

    /**
     * Modifies an existing attribute
     *
     */
    public function editAction()
    {
        $custom = new Ot_Custom();
        $attr = new Ot_Custom_Attribute();

        $get = Zend_Registry::get('getFilter');

        if (!isset($get->attributeId)) {
            throw new Ot_Exception_Input('msg-error-attributeIdNotSet');
        }

        $attribute = $attr->find($get->attributeId);

        if (is_null($attribute)) {
            throw new Ot_Exception_Data('msg-error-noAttribute');
        }

        $attribute = $attribute->toArray();

        $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);

        $config = Zend_Registry::get('config');

        if (!isset($config->app->customFieldObjects->{$attribute['objectId']})) {
            throw new Ot_Exception_Input('msg-error-objectNotSetup');
        }

        if ($this->_request->isPost()) {

            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim())
                   ->addfilter(new Zend_Filter_HtmlEntities());

            $options = array();
            if (isset($_POST['option'])) {
                foreach ($_POST['option'] as $o) {
                        if ($o != '') {
                        $options[] = $filter->filter($o);
                        }
                }
            }

            $attribute = $attr->find($get->attributeId);

            if (is_null($attribute)) {
                throw new Ot_Exception_Data('msg-error-noAttribute');
            }

            $attribute = $attribute->toArray();

            $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);

            if (isset($_POST['opt_delete'])) {
                foreach ($_POST['opt_delete'] as $opt) {
                        $key = array_search($filter->filter($opt), $attribute['options']);
                        unset($attribute['options'][$key]);
                }
            }

            $attribute['options'] = array_merge($attribute['options'], $options);

            $data = array(
                       'attributeId' => $get->attributeId,
                       'label'       => $filter->filter($_POST['label']),
                       'type'        => $filter->filter($_POST['type']),
                       'required'    => (isset($_POST['required']) ? $filter->filter($_POST['required']) : 0),
                       'direction'   => $filter->filter($_POST['direction']),
                    );

            if (($data['type'] == 'select' || $data['type'] == 'radio') && is_array($attribute['options'])) {
                    $data['options'] = $custom->convertOptionsToString($attribute['options']);
            } else {
                    $data['options'] = '';
            }

            $attr->update($data, null);

            $logOptions = array(
                        'attributeName' => 'nodeAddtributeId',
                        'attributeId'   => $data['attributeId'],
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Attribute ' . $data['label'] . ' modified', $logOptions);
            
            $this->_helper->flashMessenger->addMessage($this->view->translate('msg-info-attributeSaved', $data['label']));

            $this->_helper->redirector->gotoRoute(array('controller' => 'custom', 'action' => 'details', 'objectId' => $attribute['objectId']), 'ot', true);
        }

        $this->_helper->pageTitle('ot-custom-edit:title', $attribute['objectId']);
        $this->view->objectId          = $attribute['objectId'];
        $this->view->objectDescription = $config->app->customFieldObjects->{$attribute['objectId']};
        $this->view->attribute         = $attribute;
        $this->view->types             = $custom->getTypes();
    }

    /**
     * Deletes an attribute
     *
     */
    public function deleteAction()
    {
        $custom = new Ot_Custom();
        $attr = new Ot_Custom_Attribute();

        $get = Zend_Registry::get('getFilter');

        if (!isset($get->attributeId)) {
            throw new Ot_Exception_Input('msg-error-attributeIdNotSet');
        }

        $attribute = $attr->find($get->attributeId);

        if (is_null($attribute)) {
            throw new Ot_Exception_Data('msg-error-noAttribute');
        }

        $attribute = $attribute->toArray();

        $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);

        $config = Zend_Registry::get('config');

        if (!isset($config->app->customFieldObjects->{$attribute['objectId']})) {
            throw new Ot_Exception_Input('msg-error-objectNotSetup');
        }

        $form = Ot_Form_Template::delete('deleteAttribute');
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            $where = $attr->getAdapter()->quoteInto('attributeId = ?', $get->attributeId);
            $attr->delete($where);

            $val = new Ot_Custom_Attribute_Value();
            $val->delete($where);

            $logOptions = array(
                        'attributeName' => 'objectAttributeId',
                        'attributeId'   => $get->attributeId,
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Attribute and all values were deleted', $logOptions);

            $this->_helper->flashMessenger->addMessage($this->view->translate('msg-info-attributeDeleted', $attribute['label']));

            $this->_helper->redirector->gotoRoute(array('controller' => 'custom', 'action' => 'details', 'objectId' => $attribute['objectId']), 'ot', true);

        }

        $this->view->form              = $form;
            $this->view->attribute         = $attribute;
        $this->view->objectId          = $attribute['objectId'];
        $this->view->objectDescription = $config->app->customFieldObjects->{$attribute['objectId']};
            $this->_helper->pageTitle('ot-custom-delete:title', $attribute['objectId']);
    }
}