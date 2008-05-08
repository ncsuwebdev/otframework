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
 * @package    Admin_CustomController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Allows the management of custom attributes to certain parent nodes within
 * the application.
 *
 * @package    Admin_CustomController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_CustomController extends Internal_Controller_Action 
{
    
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
        $this->_flashMessenger->setNamespace('custom');
        
        parent::init();
    }	
	
    /**
     * Shows all available nodes to add attributes to
     */
    public function indexAction()
    {
    	$config = Zend_Registry::get('appConfig');
    	
        $this->view->title = "Custom Fields";
        
        $this->view->acl = array(
            'details'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'details'),
            );

        $objects = array();
        foreach ($config->customFieldObjects as $key => $value) {

            $objects[] = array(
               'objectId' => $key,
               'description' => $value,
            );
        }
        
        if (count($objects) != 0) {
            $this->view->javascript = array(
               'sortable.js',
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
            'add'    => $this->_acl->isAllowed($this->_role, $this->_resource, 'add'),
            'edit'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete' => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete'),
            'attributeDetails' => $this->_acl->isAllowed($this->_role, $this->_resource, 'attributeDetails'),
            );   

        $get = Zend_Registry::get('getFilter');
        if (!isset($get->objectId)) {
        	throw new Ot_Exception_Input('Object not found in query string.');
        }
        
        $config = Zend_Registry::get('appConfig');
        
        if (!isset($config->customFieldObjects->{$get->objectId})) {
        	throw new Ot_Exception_Input('Object not setup in config file');
        }
        
        $custom = new Ot_Custom();
        $attributes = $custom->getAttributesForObject($get->objectId);
        
        $this->view->attributes = $attributes;
        $this->view->title = 'Attributes for ' . $get->objectId;
        $this->view->objectId = $get->objectId;
        $this->view->objectDescription = $config->customFieldObjects->{$get->objectId};
    }
    
    /**
     * Updates the display order of the attributes from the AJAX request
     *
     */
    public function orderAttributesAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	
    	
        if ($this->_request->isPost()) {

            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim())
                   ->addfilter(new Zend_Filter_HtmlEntities())
                   ;

            $objectId = $filter->filter($_POST['objectId']);
            $order = $filter->filter($_POST['order']);
            $order = explode(',', $order);

            for ($x = 0; $x < count($order); $x++) {
                $order[$x] = (int)$order[$x];
            }

            $custom = new Ot_Custom();

            try {
                $custom->updateAttributeOrder($objectId, $order);
                echo "New order saved successfully."; 
            } catch (Exception $e) {
            	echo "Saving new order failed - " . $e->getMessage();
            }
            
            $this->_logger->setEventItem('attributeName', 'objectId');
            $this->_logger->setEventItem('attributeId', $objectId);
            $this->_logger->info($objectId . ' had attributes reordered');
        }
    }   
    
    /**
     * Shows the details of a selected attribute
     *
     */
    public function attributeDetailsAction()
    {
        $this->view->acl = array(
            'add'    => $this->_acl->isAllowed($this->_role, $this->_resource, 'add'),
            'edit'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete' => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete'),
            );  
                	
    	$get = Zend_Registry::get('getFilter');
    	
    	$custom = new Ot_Custom();
    	$attr   = new Ot_Custom_Attribute();
    	
    	if (!isset($get->attributeId)) {
    		throw new Ot_Exception_Input('Attribute ID not set in query string');
    	}
    	
    	$attribute = $attr->find($get->attributeId);

    	if (is_null($attribute)) {
    		throw new Ot_Exception_Data('Attribute not found');
    	}
    	
    	$attribute = $attribute->toArray();
    	
    	$attribute['options'] = $custom->convertOptionsToArray($attribute['options']);
    	
        $config = Zend_Registry::get('appConfig');
        
        if (!isset($config->customFieldObjects->{$attribute['objectId']})) {
            throw new Ot_Exception_Input('Object not setup in config file');
        }   
        
    	$this->view->attribute = $attribute;
        $this->view->objectId = $attribute['objectId'];
        $this->view->objectDescription = $config->customFieldObjects->{$attribute['objectId']};
    	$this->view->title = 'Attribute Details';
    }
    
    /**
     * Adds a new attribute to a node
     *
     */
    public function addAction()
    {
        $get = Zend_Registry::get('getFilter');
        if (!isset($get->objectId)) {
            throw new Ot_Exception_Input('Object not found in query string.');
        }
        
        $config = Zend_Registry::get('appConfig');
        
        if (!isset($config->customFieldObjects->{$get->objectId})) {
            throw new Ot_Exception_Input('Object not setup in config file');
        }    	
        
        $custom = new Ot_Custom();
    	
        if ($this->_request->isPost()) {
            
            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim())
                   ->addfilter(new Zend_Filter_HtmlEntities())
                   ;
                   
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
                'required'  => $filter->filter($_POST['required']),
                'direction' => $filter->filter($_POST['direction']),
                'order'     => 0,
                );
            
            $attr = new Ot_Custom_Attribute();
            
            $id = $attr->insert($data);
            
            $this->_logger->setEventItem('attributeName', 'objectId');
            $this->_logger->setEventItem('attributeId', $data['objectId']);
            $this->_logger->info('Attribute ' . $data['label'] . ' added'); 

            $this->_logger->setEventItem('attributeName', 'attributeId');
            $this->_logger->setEventItem('attributeId', $id);
            $this->_logger->info('Attribute ' . $data['label'] . ' added');             
            
            $this->_helper->redirector->gotoUrl('/admin/custom/details/?objectId=' . $data['objectId']);
            
        }
        
        $this->view->types = $custom->getTypes();
	    $this->view->title = "Add Custom Attribute to " . $get->objectId;
	    $this->view->objectId = $get->objectId;
        $this->view->objectDescription = $config->customFieldObjects->{$get->objectId};
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
            throw new Ot_Exception_Input('Attribute ID not set in query string');
        }
            
        $attribute = $attr->find($get->attributeId);
    
        if (is_null($attribute)) {
            throw new Ot_Exception_Data('Attribute not found');
        }
            
        $attribute = $attribute->toArray();         
            
        $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);
            
        $config = Zend_Registry::get('appConfig');
        
        if (!isset($config->customFieldObjects->{$attribute['objectId']})) {
            throw new Ot_Exception_Input('Object not setup in config file');
        }               
        
        if ($this->_request->isPost()) {
            
            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim())
                   ->addfilter(new Zend_Filter_HtmlEntities())
                   ;
                   

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
                throw new Ot_Exception_Data('Attribute not found');
            }
            
            $attribute = $attribute->toArray();         
            
            $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);
            
            foreach ($_POST['opt_delete'] as $opt) {
            	$key = array_search($filter->filter($opt), $attribute['options']);
            	unset($attribute['options'][$key]);
            }
            
            $attribute['options'] = array_merge($attribute['options'], $options);
            
            
            $data = array(
                       'attributeId' => $get->attributeId,
                       'label'       => $filter->filter($_POST['label']),
                       'type'        => $filter->filter($_POST['type']),
                       'required'    => $filter->filter($_POST['required']),
                       'direction'   => $filter->filter($_POST['direction']),
                    );
                    
            if (($data['type'] == 'select' || $data['type'] == 'radio') && is_array($attribute['options'])) {
            	$data['options'] = $custom->convertOptionsToString($attribute['options']);
            } else {
            	$data['options'] = '';
            }

            $attr->update($data, null);
            
            $this->_logger->setEventItem('attributeName', 'nodeAttributeId');
            $this->_logger->setEventItem('attributeId', $data['attributeId']);
            $this->_logger->info('Attribute ' . $data['label'] . ' modified');              
            
            $this->_helper->redirector->gotoUrl('/admin/custom/details/?objectId=' . $attribute['objectId']);
            
        }
        
        $this->view->title = "Edit Custom Attribute for " . $attribute['objectId'];
        $this->view->objectId = $attribute['objectId'];
        $this->view->objectDescription = $config->customFieldObjects->{$attribute['objectId']};
        $this->view->attribute = $attribute;
        $this->view->types = $custom->getTypes();         
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
            throw new Ot_Exception_Input('Attribute ID not set in query string');
        }
            
        $attribute = $attr->find($get->attributeId);
    
        if (is_null($attribute)) {
            throw new Ot_Exception_Data('Attribute not found');
        }
            
        $attribute = $attribute->toArray();         
            
        $attribute['options'] = $custom->convertOptionsToArray($attribute['options']);
            
        $config = Zend_Registry::get('appConfig');
        
        if (!isset($config->customFieldObjects->{$attribute['objectId']})) {
            throw new Ot_Exception_Input('Object not setup in config file');
        }          
        
        if ($this->_request->isPost()) {  

            $where = $attr->getAdapter()->quoteInto('attributeId = ?', $get->attributeId);
            $attr->delete($where);
            
            $val = new Ot_Custom_Attribute_Value();
            $val->delete($where);
            
            $this->_logger->setEventItem('attributeName', 'objectAttributeId');
            $this->_logger->setEventItem('attributeId', $get->attributeId);
            $this->_logger->info('Attribute and all values were deleted');             
                        
            
            $this->_helper->redirector->gotoUrl('/admin/custom/details/?objectId=' . $attribute['objectId']);
        	
        }
        
	    $this->view->attribute = $attribute;
        $this->view->objectId = $attribute['objectId'];
        $this->view->objectDescription = $config->customFieldObjects->{$attribute['objectId']};
	    $this->view->title = 'Delete Attribute from ' . $attribute['objectId'];
    }
	
}