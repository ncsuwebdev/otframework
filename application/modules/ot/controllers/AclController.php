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
 * @package    Ot_AclController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manages all access control the the application.  Allows the user to build
 * custom roles.
 *
 * @package    Ot_AclController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_AclController extends Zend_Controller_Action
{
    
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
            'add'                => $this->_helper->hasAccess('add'),
            'edit'               => $this->_helper->hasAccess('edit'),
            'application-access' => $this->_helper->hasAccess('application-access'),
            'remote-access'      => $this->_helper->hasAccess('remote-access'),
            'delete'             => $this->_helper->hasAccess('delete'),
        );
            
        $this->view->guestHasAccess = $this->_helper->hasAccess('index', 'ot_api', $this->_helper->configVar('defaultRole'));
        
        $role = new Ot_Model_DbTable_Role();
        $this->view->defaultRole = $role->find($this->_helper->configVar('defaultRole'));
            
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
        $this->_helper->pageTitle("ot-acl-index:title");
    }
    
    /**
     * Shows the details of a role
     *
     */
    public function detailsAction()
    {
        $this->view->acl = array(
            'index'              => $this->_helper->hasAccess('index'),
            'edit'               => $this->_helper->hasAccess('edit'),
            'delete'             => $this->_helper->hasAccess('delete'),
            'application-access' => $this->_helper->hasAccess('application-access'),
            'remote-access'      => $this->_helper->hasAccess('remote-access'),
            );

        $get = Zend_Registry::get('getFilter');
        
        $this->view->guestHasAccess = $this->_helper->hasAccess('index', 'ot_api', $this->_helper->configVar('defaultRole'));
        
        $role = new Ot_Model_DbTable_Role();
        $this->view->defaultRole = $role->find($this->_helper->configVar('defaultRole'));

        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }
        
        $thisRole = $role->find($get->roleId); 
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }
        
        $this->view->role = $thisRole->toArray();
        
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
        unset($r);
        
        $this->view->resources = $resources;
        
        $remoteAcl = new Ot_Acl('remote');
        
        $remoteResources = $remoteAcl->getRemoteResources($thisRole['roleId']);
        
        foreach ($remoteResources as &$r) {
            foreach ($r as &$c) {
                $c['someAccess'] = false;
                foreach ($c['part'] as $p) {
                    if ($p['access']) {
                        $c['someaccess'] = true;
                    }
                }
            }
        }
        unset($r);
        
        $this->view->remoteResources = $remoteResources;
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
                
        if ($thisRole['inheritRoleId'] != 0) {
            $inheritRole = $role->find($thisRole['inheritRoleId']);
            $this->view->inheritRole = $inheritRole->name;
        }
        $this->_helper->pageTitle("ot-acl-details:title");

    }    

    /**
     * Add a new role to the ACL
     *
     */
    public function addAction()
    {   
        $role = new Ot_Model_DbTable_Role();
        
        $form = $role->form();
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
            
                $data = array(
                    'name'          => preg_replace('/[^a-z0-9]/i', '_', $form->getValue('name')),
                    'inheritRoleId' => $form->getValue('inheritRoleId'),
                    'editable'      => 1,
                );

                $roleId = $role->insert($data);
                
                $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $roleId,
                );
                    
                $this->_helper->log(Zend_Log::INFO, 'Role ' . $data['name'] . ' was added', $logOptions);
    
                $this->_helper->redirector->gotoUrl('/ot/acl/details?roleId=' . $roleId);
            } else {
                $this->_helper->messenger->addError('msg-error-invalidForm');
            }
        }
        
        $this->view->form = $form;

        $this->_helper->pageTitle("ot-acl-add:title");
    }

    /**
     * Edit an existing role in the ACL
     *
     */
    public function editAction()
    {
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }
                
        $role = new Ot_Model_DbTable_Role();
        
        $thisRole = $role->find($get->roleId);
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }
        
        if ($thisRole->editable != 1) {
            throw new Ot_Exception_Access('msg-error-unallowedRoleEdit');
        }
        
        $form = $role->form($thisRole->toArray());
        
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $data = array(
                    'roleId'        => $get->roleId,
                    'name'          => preg_replace('/[^a-z0-9]/i', '_', $form->getValue('name')),
                    'inheritRoleId' => $form->getValue('inheritRoleId'),
                );
    
                $role->update($data, null);
                
                $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $data['roleId'],
                );
                    
                $this->_helper->log(Zend_Log::INFO, 'Role ' . $data['name'] . ' was modified', $logOptions);
    
                $this->_helper->redirector->gotoUrl('/ot/acl/details/?roleId=' . $data['roleId']);
            } else {
                $this->_helper->messenger->addError('msg-error-invalidForm');
            }

        }

        $this->view->role = $thisRole;
        $this->view->form = $form;
                              
        $this->_helper->pageTitle("ot-acl-edit:title");
    }

    /**
     * Allows a user to set access rules for a role for the application.
     *
     */
    public function applicationAccessAction()
    {
    	$this->view
             ->headScript()
             ->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.json.js');
             
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }
                
        $role = new Ot_Model_DbTable_Role();
        
        $thisRole = $role->find($get->roleId);
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }
        
        if ($thisRole->editable != 1) {
            throw new Ot_Exception_Access('msg-error-unallowedRoleEdit');
        }

        if ($thisRole->inheritRoleId != 0) {
            $this->view->inheritRole = $role->find($thisRole->inheritRoleId);
        }
        
        if ($this->_request->isPost()) {
            
            $rules = $this->_processAccessList($_POST, $thisRole->inheritRoleId);
                            
            $role->assignRulesForRole($get->roleId, 'application', $rules);
            
            $logOptions = array(
                'attributeName' => 'accessRole',
                'attributeId'   => $thisRole->roleId,
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Role ' . $thisRole->name . ' was modified', $logOptions);

            $this->_helper->redirector->gotoUrl('/ot/acl/details/?roleId=' . $thisRole->roleId);

        }

        $this->view->children = $this->_acl->getChildrenOfRole($thisRole->roleId);

        $resources = $this->_acl->getResources($thisRole->roleId);
        
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
        $this->view->role = $thisRole;
            
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
                      
        $this->_helper->pageTitle("ot-acl-applicationAccess:title");  
    }
    
    /**
     * Allows a user to set access rules for a role for remote access
     *
     */
    public function remoteAccessAction()
    {
    	$this->view
             ->headScript()
             ->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.json.js');
             
        $get = Zend_Registry::get('getFilter');
        
        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }
                
        $role = new Ot_Model_DbTable_Role();
        
        $thisRole = $role->find($get->roleId);
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }
        
        if ($thisRole->editable != 1) {
            throw new Ot_Exception_Access('msg-error-unallowedRoleEdit');
        }

        if ($thisRole->inheritRoleId != 0) {
            $this->view->inheritRole = $role->find($thisRole->inheritRoleId);
        }
        
        $remoteAcl = new Ot_Acl('remote');
        
        if ($this->_request->isPost()) {

            $rules = array();
            $rules = $this->_processAccessList($_POST, $thisRole->inheritRoleId, 'remote');
                
            $role->assignRulesForRole($thisRole->roleId, 'remote', $rules);
            
            $logOptions = array(
                'attributeName' => 'accessRole',
                'attributeId'   => $thisRole->roleId,
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Role ' . $thisRole->name . ' was modified', $logOptions);

            $this->_helper->redirector->gotoUrl('/ot/acl/details/?roleId=' . $thisRole->roleId);

        }

        $this->view->children = $this->_acl->getChildrenOfRole($thisRole->roleId);

        $resources = $remoteAcl->getRemoteResources($thisRole->roleId);
        
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
        $this->view->role = $thisRole;
            
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
                      
        $this->_helper->pageTitle("ot-acl-remoteAccess:title");  
    }    
      
    /**
     * Deletes a role from the ACL
     *
     */
    public function deleteAction()
    { 
        $get = Zend_Registry::get('getFilter');

        if (!isset($get->roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }  
                
        $availableRoles = $this->_acl->getAvailableRoles();
        
        if (!isset($availableRoles[$get->roleId])) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }
        
        $thisRole = $availableRoles[$get->roleId];
        
        if ($thisRole['editable'] != 1) {
            throw new Ot_Exception_Access('msg-error-unallowedRoleDelete');
        }
        
        $account = new Ot_Model_DbTable_Account();
        $affectedAccounts = $account->getAccountsForRole($get->roleId);
        
        $defaultRole = $this->_helper->configVar('defaultRole');
        
        if (!isset($availableRoles[$defaultRole])) {
            throw new Ot_Exception_Data('msg-error-noDefaultRole');
        }
        
        if ($defaultRole == $get->roleId) {
            throw new Ot_Exception_Data('msg-error-deleteDefaultRole');
        }
                
        $this->view->accountRoleChange = (count($affectedAccounts) != 0);
        $this->view->defaultRole = $availableRoles[$defaultRole]['name'];
                
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
                throw new Ot_Exception_Data($this->view->translate('msg-error-dependedRoleCannotDelete', $roleList));
            }

            $role = new Ot_Model_DbTable_Role();
            
            $dba = $role->getAdapter();
            $dba->beginTransaction();
            
            try {
                $role->deleteRole($get->roleId);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }
            
            $accountRoles = new Ot_Model_DbTable_AccountRoles();
            
            // aList is an array of all the affected accountIds
            $aList = array();
            if (count($affectedAccounts) > 0) {
                foreach($affectedAccounts as $a) {
                    $aList[] = $a->accountId;
                }
                
                if(count($aList) > 0) {
                    
                    // get a list of all the accounts that still have a role after removing one so we can diff()
                    // it to find the accounts that no longer have a role
                    $accountRolesDba = $accountRoles->getAdapter();
                    $where = $accountRolesDba->quoteInto('accountId IN(?)', $aList);
                
                    $affectedAccountsStillWithRoles = $accountRoles->fetchAll($where);
                
                    $affectedAccountsStillWithRolesIds = array();
                    foreach($affectedAccountsStillWithRoles as $a) {
                        $affectedAccountsStillWithRolesIds[] = $a->accountId;
                    }
                    
                    // here's the list of accounts that don't have a role, so we have to add $defaultRole to them.
                    $affectedAccountsWithNoRoles = array_diff($aList, $affectedAccountsStillWithRolesIds);
                    
                    foreach ($affectedAccountsWithNoRoles as $a) {
                        $accountRoles->insert(
                            array(
                                'accountId' => $a,
                                'roleId'    => $defaultRole,
                            )
                        );
                    }
                    
                }
            }
            
            $dba->commit();

            $logOptions = array(
                'attributeName' => 'accessRole',
                'attributeId'   => $get->roleId,
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Role ' . $thisRole['name'] . ' was deleted', $logOptions);

            $this->_helper->redirector->gotoUrl('/ot/acl/');
        }      
        
        $this->view->role = $thisRole;
        $this->view->form = $form;
        
        $this->_helper->pageTitle("ot-acl-delete:title");
    }


      
    
    /**
     * Processes the access list passed through adding and editing a role
     *
     * @param array $data
     * @param string $inheritRoleName
     * @return array
     */
    protected function _processAccessList($data, $inheritRoleId, $scope = 'application')
    {
        if ($scope == 'remote') {
            $acl = new Ot_Acl('remote');
            
            $resources = $acl->getRemoteResources($inheritRoleId);
            
        } else {
            $acl = new Ot_Acl();
            
            $resources = $acl->getResources($inheritRoleId);
            $acl = $this->_acl;
        }
       
        if ($inheritRoleId == 0) {
            $inheritRoleId = null;
        }
        
        $rules = array();
        
        foreach ($resources as $module => $controllers) {
            foreach ($controllers as $controller => $actions) {

                $resource = strtolower($module . '_' . $controller);

                if (isset($data[$module][$controller]['all'])) {
                    if ($data[$module][$controller]['all'] == 'allow') {
                        if (!$acl->isAllowed($inheritRoleId, $resource)) {
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
                                        'privilege' => $action,
                                    );
                                }
                            }
                        }
                    } else {
                        if ($acl->isAllowed($inheritRoleId, $resource)) {
                            $rules[] = array(
                                'type'      => 'deny',
                                'resource'  => $resource,
                                'privilege' => '*',
                            );
                        }

                        $parts = array_keys($actions['part']);
                        
                        foreach ($parts as $action) {
                            if (isset($data[$module][$controller]['part'][$action])) {
                                if ($data[$module][$controller]['part'][$action] == 'allow'
                                    && !$acl->isAllowed($inheritRoleId, $resource, $action)) {
                                    $rules[] = array(
                                        'type'      => 'allow',
                                        'resource'  => $resource,
                                        'privilege' => $action,
                                    );
                                }
                            }
                        }
                    }
                } else {
                    $parts = array_keys($actions['part']);
                    
                    foreach ($parts as $action) {                       
                        if (isset($data[$module][$controller]['part'][$action])) {
                            if ($data[$module][$controller]['part'][$action] == 'allow'
                                && !$acl->isAllowed($inheritRoleId, $resource, $action)) {
                                $rules[] = array(
                                    'type'      => 'allow',
                                    'resource'  => $resource,
                                    'privilege' => $action,
                                );
                            }

                            if ($data[$module][$controller]['part'][$action] == 'deny'
                                && $acl->isAllowed($inheritRoleId, $resource, $action)) {
                                $rules[] = array(
                                    'type'      => 'deny',
                                    'resource'  => $resource,
                                    'privilege' => $action,
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