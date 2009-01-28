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
class Admin_AclController extends Zend_Controller_Action 
{
	/**
	 * Path to the config file
	 *
	 * @var unknown_type
	 */
	protected $_configFilePath = '';
	
	
	/**
     * The ACL object for the application.  It's kept here because basically 
     * every method in this class uses it.
     */
    protected $_acl;
	
    /**
     * Runs when the class is initialized.  Sets up the view instance and the
     * various models used in the class.
     *
     */
    public function init()
    {
        $this->_configFilePath = Zend_Registry::get('configFilePath');
        
        $this->_acl = Zend_Registry::get('acl');
        
        parent::init();
    }

    /**
     * List of all existing roles in the application.
     *
     */
    public function indexAction()
    {
        $this->view->acl = array(
            'add'    => $this->_helper->hasAccess('add'),
            'edit'   => $this->_helper->hasAccess('edit'),
            'delete' => $this->_helper->hasAccess('delete'),
            );

        $roles = $this->_acl->getAvailableRoles();
      
        foreach ($roles as &$r) {
            
        	$children = $this->_acl->getChildrenOfRole($r['roleId']);
            
            if (count($children) > 0) {
            	$r['inheritedFrom'] = 1;
            } else {
            	$r['inheritedFrom'] = 0;
            }
        }
                
        $this->view->roles = $roles;
        $this->_helper->pageTitle("Manage Access Roles");
    }
    
    /**
     * Shows the details of a role
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'  => $this->_helper->hasAccess('index'),
            'edit'   => $this->_helper->hasAccess('edit'),
            'delete' => $this->_helper->hasAccess('delete')
            );

        $get = Zend_Registry::get('getFilter');

        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('roleId not set in query string');
        }
        
        $role = new Ot_Role();
        
        $thisRole = $role->find($get->roleId); 
        if (is_null($thisRole)) {
        	throw new Ot_Exception_Data('Role not found.');
        }
        
        $this->view->role  = $thisRole->toArray();
        
 		$resources = $this->_acl->getResources($thisRole['roleId']);
        
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
        
        $this->view->resources = $resources;
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/public/ot/css/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/ot/scripts/jquery.plugin.tipsy.js');
                
        if ($thisRole['inheritRoleId'] != 0) {
        	$inheritRole = $role->find($thisRole['inheritRoleId']);
        	
        	$this->view->inheritRole = $inheritRole->name;
        }
        $this->_helper->pageTitle("Access Role Details");

    }    

    /**
     * Add a new role to the ACL
     *
     */
    public function addAction()
    {   
        $roles = $this->_acl->getAvailableRoles();

        $temp = array();
        foreach ($roles as $r) {
            $temp[$r['roleId']] = $r['name'];
        }

        $roles = $temp;

        $this->view->roles = array(0 => 'No Inheritance') + $roles;      

        if ($this->_request->isPost()) {
            
            $post = Zend_Registry::get('postFilter');

            if (!isset($post->roleName)) {
            	throw new Ot_Exception_Input('Role Name can not be blank');
            }
            
            $data = array(
                'name'          => preg_replace('/[^a-z0-9]/i', '_', $post->roleName),
                'inheritRoleId' => $post->inheritRoleId,
                'editable'      => 1
                );

            $data['rules'] = $this->_processAccessList($_POST, $data['inheritRoleId']);

            $role = new Ot_Role();
            $roleId = $role->insert($data);
            
            $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $roleId,
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Role ' . $data['name'] . ' was added', $logOptions);

            $this->_helper->redirector->gotoUrl('/admin/acl/details?roleId=' . $roleId);

        }
        
        $get = Zend_Registry::get('getFilter');

        if (isset($get->roleName)) {
            $this->view->roleName = $get->roleName;
        }

        $inheritRoleId = (isset($get->inheritRoleId)) ? $get->inheritRoleId : 0;
                
        $this->view->inheritRoleId = $inheritRoleId;

        $this->view->action    = 'add';
        $resources = $this->_acl->getResources($inheritRoleId);
        
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
        
        $this->view->resources = $resources;        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/public/ot/css/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/ot/scripts/jquery.plugin.tipsy.js');
        
        $this->_helper->pageTitle("Manage Access Roles");
    }

    /**
     * Edit an existing role in the ACL
     *
     */
    public function editAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->roleId)) {
        	throw new Ot_Exception_Input('Role ID not set in query string.');
        }
        
    	$availableRoles = $this->_acl->getAvailableRoles();
    	
    	if (!isset($availableRoles[$get->roleId])) {
    		throw new Ot_Exception_Data('Role not found');
    	}
    	
    	$role = $availableRoles[$get->roleId];
    	
    	if ($role['editable'] != 1) {
    		throw new Ot_Exception_Access('You are not allowed to edit this role.');
    	}
    	
    	$inheritRoleId = $role['inheritRoleId'];
        
        $roleName = (isset($get->roleName)) ? $get->roleName : $role['name'];

        $temp = array();
        foreach ($availableRoles as $r) {
            $temp[$r['roleId']] = $r['name'];
        }

        $roles = $temp;

        $this->view->roles = array(0 => 'No Inheritance') + $roles;

        if ($this->_request->isPost()) {
        	
            $post = Zend_Registry::get('postFilter');
            
            if (!isset($post->roleName)) {
                throw new Ot_Exception_Input('Role Name can not be blank');
            }
            
            $data = array(
            	'roleId'        => $get->roleId,
                'name'          => preg_replace('/[^a-z0-9]/i', '_', $post->roleName),
                'inheritRoleId' => $post->inheritRoleId,
                );

            $data['rules'] = $this->_processAccessList($_POST, $data['inheritRoleId']);

			$role = new Ot_Role();
			$role->update($data, null);
			
            $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $data['roleId'],
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Role ' . $data['name'] . ' was modified', $logOptions);

            $this->_helper->redirector->gotoUrl('/admin/acl/details/?roleId=' . $data['roleId']);

        }

        $this->view->children = $this->_acl->getChildrenOfRole($role['roleId']);

        $resources = $this->_acl->getResources($role['roleId']);
        
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

        $this->view->roleName         = $roleName;
        $this->view->roleId           = $role['roleId'];
        $this->view->inheritRoleId    = $inheritRoleId;
        $this->view->resources        = $resources;
            
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/public/ot/css/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/ot/scripts/jquery.plugin.tipsy.js');
          
        $this->_helper->pageTitle("Edit Role");
    }

    /**
     * Deletes a role from the ACL
     *
     */
    public function deleteAction()
    { 
        $get = Zend_Registry::get('getFilter');

        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('Role id not set');
        }  
                
        $availableRoles = $this->_acl->getAvailableRoles();
    	
    	if (!isset($availableRoles[$get->roleId])) {
    		throw new Ot_Exception_Data('Role not found');
    	}
    	
    	$thisRole = $availableRoles[$get->roleId];
    	
    	if ($thisRole['editable'] != 1) {
    		throw new Ot_Exception_Access('You are not allowed to delete this role.');
    	}
    	        
        $form = Ot_Form_Template::delete('deleteRole');
        
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            
            $inheritedRoles = array();
            $inheritedRoles = $this->_acl->getChildrenOfRole($get->roleId);
            
            $roleList = array();
            
            foreach ($inheritedRoles as $key => $value) {
            	$roleList[] = $key;
            }

            $roleList = implode(', ', $roleList);
            
            if (count($inheritedRoles) > 0) {
				throw new Ot_Exception_Data('This role is depended upon by other roles (' . $roleList . ') and cannot be deleted as long as the other roles depend upon it.');
            }

            $role = new Ot_Role();
            $role->deleteRole($get->roleId);

            $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $get->roleId,
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Role ' . $thisRole['name'] . ' was deleted', $logOptions);

            $this->_helper->redirector->gotoUrl('/admin/acl/');
        }      
        
        $this->view->role = $thisRole;
        $this->view->form = $form;
        
        $this->_helper->pageTitle("Delete Access Role");
    }


    
    /**
     * Processes the access list passed through adding and editing a role
     *
     * @param array $data
     * @param string $inheritRoleName
     * @return array
     */
    protected function _processAccessList($data, $inheritRoleId)
    {
        $resources = $this->_acl->getResources($inheritRoleId);
       
        if ($inheritRoleId == 0) {
        	$inheritRoleId = null;
        }
        
        $rules = array();

        foreach ($resources as $module => $controllers) {
            foreach ($controllers as $controller => $actions) {

                $resource = strtolower($module . '_' . $controller);

                if (isset($data[$module][$controller]['all'])) {
                    if ($data[$module][$controller]['all'] == 'allow') {
                        if (!$this->_acl->isAllowed($inheritRoleId, $resource)) {
                            $rules[] = array(
                            	'type'      => 'allow',
                                'resource'  => $resource,
                                'privilege' => '*'
                                );
                        }

                        $parts = array_keys($actions['part']);
                        
                        foreach ($parts as $action) {
                            if (isset($data[$module][$controller]['part'][$action])) {
                                if ($data[$module][$controller]['part'][$action] == 'deny') {
                                    $rules[] = array(
                            	        'type'      => 'deny',
                                        'resource'  => $resource,
                                        'privilege' => $action
                                        );
                                }
                            }
                        }
                    } else {
                        if ($this->_acl->isAllowed($inheritRoleId, $resource)) {
                            $rules[] = array(
                            	'type'      => 'deny',
                                'resource'  => $resource,
                                'privilege' => '*'
                                );
                        }

                        $parts = array_keys($actions['part']);
                        
                        foreach ($parts as $action) {
                            if (isset($data[$module][$controller]['part'][$action])) {
                                if ($data[$module][$controller]['part'][$action] == 'allow' && !$this->_acl->isAllowed($inheritRoleId, $resource, $action)) {
                                    $rules[] = array(
                            	        'type'      => 'allow',
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
                            if ($data[$module][$controller]['part'][$action] == 'allow' && !$this->_acl->isAllowed($inheritRoleId, $resource, $action)) {
                                $rules[] = array(
                            	    'type'      => 'allow',
                                    'resource'  => $resource,
                                    'privilege' => $action
                                    );
                            }

                            if ($data[$module][$controller]['part'][$action] == 'deny' && $this->_acl->isAllowed($inheritRoleId, $resource, $action)) {
                                $rules[] = array(
                            		'type'      => 'deny',
                            		'resource'  => $resource,
                                    'privilege' => $action
                                    );
                            }
                        }
                    }
                }
            }
        }

        return $rules;
    }
}