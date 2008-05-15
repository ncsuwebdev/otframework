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
 * @package    Admin_AclController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Manages all access control the the application.  Allows the user to build
 * custom roles.
 *
 * @package    Admin_AclController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Admin_AclController extends Internal_Controller_Action 
{
    /**
     * Authz adapter
     *
     * @var mixed
     */
	protected $_authzAdapter = null;
	
	/**
	 * Path to the config file
	 *
	 * @var unknown_type
	 */
	protected $_configFilePath = '';
	
    /**
     * Runs when the class is initialized.  Sets up the view instance and the
     * various models used in the class.
     *
     */
    public function init()
    {
        $config = Zend_Registry::get('appConfig');

        $this->_authzAdapter = new $config->authorization(Zend_Auth::getInstance()->getIdentity());

        $configFiles= Zend_Registry::get('configFiles');
        
        $this->_configFilePath = $configFiles['acl'];
        
        parent::init();
    }

    /**
     * List of all existing roles in the application.
     *
     */
    public function indexAction()
    {
        $this->view->acl = array(
            'add'    => $this->_acl->isAllowed($this->_role, $this->_resource, 'add'),
            'edit'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete' => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete'),
            );

        $this->view->roles = $this->_acl->getAvailableRoles();

        if (count($this->view->roles) != 0) {
            $this->view->javascript = 'sortable.js';
        }

        $this->view->title = "Manage Access Roles";
    }

    /**
     * Add a new role to the ACL
     *
     */
    public function addAction()
    {   
        if (!is_writable($this->_configFilePath)) {
            throw new Exception('ACL file is not writable, therefore you can not add roles to it.  Contact system administrator for assistance');
        }

        $roles = $this->_acl->getAvailableRoles();

        $temp = array();
        foreach ($roles as $r) {
            if ($r['editable'] == 1) {
                $temp[$r['name']] = $r['name'];
            }
        }

        $roles = $temp;

        $this->view->roles = array_merge(array('none' => 'No Inheritance'), $roles);

        if ($this->_request->isPost()) {

        	$filterRules = array(
        	   '*' => array(
        	       'StringTrim',
        	       'StripTags',
        	   ),
            );
            
            $filter = new Zend_Filter_Input($filterRules, array(), $_POST);

            if (!isset($filter->roleName)) {
            	throw new Ot_Exception_Input('Role Name can not be blank');
            }
            
            $roleName        = $filter->roleName;
            $inheritRoleName = ($filter->inheritRoleName == 'none') ? null : $filter->inheritRoleName;

            $data = array(
                'name'     => preg_replace('/[^a-z0-9]/i', '_', $roleName),
                'inherit'  => $inheritRoleName,
                'editable' => 1
                );

            $data = array_merge($data, $this->_processAccessList($_POST, $inheritRoleName));

            $this->_acl->addCustomRole($data, $this->_configFilePath);

            $this->_logger->setEventItem('attributeName', 'accessRole');
            $this->_logger->setEventItem('attributeId', $data['name']);
            $this->_logger->info($data['roleName'] . ' was added as a role');

            $this->_helper->redirector->gotoUrl('/admin/acl/details?originalRoleName=' . $data['name']);

        }
        
        $getFilter = Zend_Registry::get('getFilter');

        $roleName        = '';
        $inheritRoleName = '';

        if (isset($getFilter->roleName)) {
            $this->view->roleName = $getFilter->roleName;
        }

        $inheritRoleName = '';
        
        if (isset($getFilter->inheritRoleName)) {
            $inheritRoleName = ($getFilter->inheritRoleName == 'none') ? '' : $getFilter->inheritRoleName;
        }
        
        $this->view->inheritRoleName = $inheritRoleName;

        $this->view->action    = 'add';
        $this->view->resources = $this->_acl->getResources($inheritRoleName);
        $this->view->title     = "Manage Access Roles";
    }

    /**
     * Edit an existing role in the ACL
     *
     */
    public function editAction()
    {
        if (!is_writable($this->_configFilePath)) {
            throw new Exception('ACL file is not writable, therefore you can not add roles to it.  Contact system administrator for assistance');
        }
            	
    	$availableRoles = $this->_acl->getAvailableRoles();

        $temp = array();
        foreach ($availableRoles as $r) {
            $temp[$r['name']] = $r['name'];
        }

        $roles = $temp;

        $this->view->roles = array_merge(array('none' => 'No Inheritance'), $roles);

        if ($this->_request->isPost()) {
        	
            $filterRules = array(
               '*' => array(
                   'StringTrim',
                   'StripTags',
               ),
            );
            
            $filter = new Zend_Filter_Input($filterRules, array(), $_POST);

            if (!isset($filter->roleName)) {
                throw new Ot_Exception_Input('Role Name can not be blank');
            }
            
            $roleName        = $filter->roleName;
            $inheritRoleName = ($filter->inheritRoleName == 'none') ? null : $filter->inheritRoleName;
            $originalRoleName = $filter->originalRoleName;

            $data = array(
                'newName'  => $roleName,
                'name'     => $originalRoleName,
                'inherit'  => $inheritRoleName,
                'editable' => 1
                );

            $result = $this->_processAccessList($_POST, $inheritRoleName);

            $data = array_merge($data, $result);

            $dba = Zend_Registry::get('dbAdapter');
            $dba->beginTransaction();

            try {
                $this->_acl->editCustomRole($data, $this->_configFilePath);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            $this->_logger->setEventItem('attributeName', 'accessRole');
            $this->_logger->setEventItem('attributeId', $data['name']);
            $this->_logger->info($data['name'] . ' was modified');

            try {
                $users = $this->_authzAdapter->getUsers($originalRoleName);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            if ($this->_authzAdapter->manageLocally()) {
                foreach ($users as $u) {

                    try {
                        $this->_authzAdapter->editUser($u['userId'], $roleName);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }
                }
            }

            $dba->commit();

            $this->_helper->redirector->gotoUrl('/admin/acl/details/?originalRoleName=' . $roleName);

        } else {
            $getFilter = Zend_Registry::get('getFilter');

            $originalRoleName = '';
            $inheritRoleName  = '';
            $roleName         = '';

            if (isset($getFilter->roleName)) {
                $roleName = $getFilter->roleName;
            }

            if (isset($getFilter->inheritRoleName)) {
                $inheritRoleName = ($getFilter->inheritRoleName == 'none') ? '' : $getFilter->inheritRoleName;
            }

            if (!isset($getFilter->originalRoleName)) {
                throw new Ot_Exception_Input('Role name not set');
            }

            $originalRoleName = $getFilter->originalRoleName;
            $role             = null;

            foreach ($availableRoles as $r) {
                if ($originalRoleName == $r['name']) {
                    $role = $r;
                }
            }

            if (is_null($role)) {
                throw new Ot_Exception_Input('Role Not Found');
            }

            if (!(boolean)$role['editable']) {
                throw new Ot_Exception_Input('The role passed is not editable');
            }

            $children = $this->_acl->getChildrenOfRole($originalRoleName);

            $temp = array();
            foreach ($children as $key => $value) {
                $t = array();
                $t['name'] = $key;
                $t['from'] = implode(' via ', array_merge(array($key), array_diff(array_reverse($value), array($originalRoleName))));

                $temp[] = $t;
            }

            $this->view->children = $temp;

            $resources = $this->_acl->getResources($role['name']);
            
            foreach ($resources as &$r) {
            	foreach ($r as &$c) {
	            	$c['someAccess'] = false;
	            	foreach ($c['part'] as $p) {
	            		if ($p['access']) {
	            			$c['someaccess'] = true;
	            		}
	            	}
            	}
            }
            
            

            $this->view->originalRoleName = $originalRoleName;
            $this->view->roleName         = ($roleName == '') ? $originalRoleName : $roleName;
            $this->view->inheritRoleName  = ($inheritRoleName == '') ? $role['inherit'] : $inheritRoleName;
            $this->view->action           = 'edit';
            $this->view->resources        = $resources;
            $this->view->title            = "Edit Role";
        }
    }

    /**
     * Deletes a role from the ACL
     *
     */
    public function deleteAction()
    { 
        if (!is_writable($this->_configFilePath)) {
            throw new Exception('ACL file is not writable, therefore you can not add roles to it.  Contact system administrator for assistance');
        }

        if ($this->_request->isPost()) {
            $filterRules = array(
               '*' => array(
                   'StringTrim',
                   'StripTags',
               ),
            );
            
            $filter = new Zend_Filter_Input($filterRules, array(), $_POST);

            if (!isset($filter->originalRoleName)) {
                throw new Ot_Exception_Input('Role Name can not be blank');
            }

            $this->_acl->deleteCustomRole($filter->originalRoleName, $this->_configFilePath);

            $this->_logger->setEventItem('attributeName', 'accessRole');
            $this->_logger->setEventItem('attributeId', $filter->originalRoleName);
            $this->_logger->info($filter->originalRoleName . ' was deleted as a role');

            $this->_helper->redirector->gotoUrl('/admin/acl/');
        }
        
        $getFilter = Zend_Registry::get('getFilter');

        $originalRoleName = '';

        if (!isset($getFilter->originalRoleName)) {
            throw new Ot_Exception_Input('Role name not set');
        }        
        
        $this->view->originalRoleName = $getFilter->originalRoleName;
        $this->view->title            = "Delete Access Role";
    }

    /**
     * Shows the details of a role
     *
     */
    public function detailsAction()
    {
        $availableRoles = $this->_acl->getAvailableRoles();

        $this->view->acl = array(
            'edit'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete' => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete'),
            );

        $getFilter = Zend_Registry::get('getFilter');

        $originalRoleName = '';

        if (!isset($getFilter->originalRoleName)) {
            throw new Ot_Exception_Input('Role name not set');
        }

        $originalRoleName = $getFilter->originalRoleName;
        $role             = null;

        foreach ($availableRoles as $r) {
            if ($originalRoleName == $r['name']) {
                $role = $r;
                break;
            }
        }

        if (is_null($role)) {
            throw new Ot_Exception_Data('Role Not Found');
        }

        $this->view->role  = $role;
        $this->view->title = "Access Role Details";

    }
    
    /**
     * Processes the access list passed through adding and editing a role
     *
     * @param array $data
     * @param string $inheritRoleName
     * @return array
     */
    protected function _processAccessList($data, $inheritRoleName)
    {
        $resources = $this->_acl->getResources($inheritRoleName);

        $allow = array();
        $deny  = array();

        foreach ($resources as $module => $controllers) {
            foreach ($controllers as $controller => $actions) {

                $resource = strtolower($module . '_' . $controller);

                if (isset($data[$module][$controller]['all'])) {
                    if ($data[$module][$controller]['all'] == 'allow') {
                        if (!$this->_acl->isAllowed($inheritRoleName, $resource)) {
                            $allow[] = array(
                                'resource'  => $resource,
                                'privilege' => '*'
                                );
                        }

                        $parts = array_keys($actions['part']);
                        
                        foreach ($parts as $action) {
                            if (isset($data[$module][$controller]['part'][$action])) {
                                if ($data[$module][$controller]['part'][$action] == 'deny') {
                                    $deny[] = array(
                                        'resource'  => $resource,
                                        'privilege' => $action
                                        );
                                }
                            }
                        }
                    } else {
                        if ($this->_acl->isAllowed($inheritRoleName, $resource)) {
                            $deny[] = array(
                                'resource'  => $resource,
                                'privilege' => '*'
                                );
                        }

                        $parts = array_keys($actions['part']);
                        
                        foreach ($parts as $action) {
                            if (isset($data[$module][$controller]['part'][$action])) {
                                if ($data[$module][$controller]['part'][$action] == 'allow' && !$this->_acl->isAllowed($inheritRoleName, $resource, $action)) {
                                    $allow[] = array(
                                        'resource'  => $resource,
                                        'privilege' => $action
                                        );
                                }
                            }
                        }
                    }
                } else {
                    $parts = array_keys($actions['part']);
                    
                    foreach ($parts as $action) {                       
                        if (isset($data[$module][$controller]['part'][$action])) {
                            if ($data[$module][$controller]['part'][$action] == 'allow' && !$this->_acl->isAllowed($inheritRoleName, $resource, $action)) {
                                $allow[] = array(
                                    'resource'  => $resource,
                                    'privilege' => $action
                                    );
                            }

                            if ($data[$module][$controller]['part'][$action] == 'deny' && $this->_acl->isAllowed($inheritRoleName, $resource, $action)) {
                                $deny[] = array(
                                    'resource'  => $resource,
                                    'privilege' => $action
                                    );
                            }
                        }
                    }
                }
            }
        }

        $ret = array(
            'allows' => $allow,
            'denys'  => $deny
            );

        return $ret;
    }
    



}