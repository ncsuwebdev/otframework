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
            'details'            => $this->_helper->hasAccess('details'),
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


        $role = new Ot_Model_DbTable_Role();
        $thisDefaultRole = $role->find($this->_helper->configVar('defaultRole'));


        $this->_helper->pageTitle("ot-acl-index:title");

        $this->view->assign(array(
            'defaultRole'    => $thisDefaultRole,
            'roles'          => $roles,
            'guestHasAccess' => $this->_helper->hasAccess('index', 'ot_api', $this->_helper->configVar('defaultRole')),
        ));

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

        $roleId = $this->_getParam('roleId', null);

        if (is_null($roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }

        $role = new Ot_Model_DbTable_Role();

        $thisRole = $role->find($roleId);
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }

        $inheritRoleName = '';

        if ($thisRole['inheritRoleId'] != 0) {
            $inheritRole = $role->find($thisRole['inheritRoleId']);
            $inheritRoleName = $inheritRole->name;
        }

        $defaultRole = $role->find($this->_helper->configVar('defaultRole'));

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
            unset($c);
        }
        unset($r);


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
        
        if ($this->_request->isPost()) {

            if (!in_array($_POST['scope'], array('application', 'remote'))) {
                throw new Ot_Exception('Scope not found.');            
            }
            
            $scope = $_POST['scope'];  
            unset($_POST['scope']);
            
            $rules = $this->_processAccessList($_POST, $thisRole->inheritRoleId, $scope);
            
            $role->assignRulesForRole($thisRole->roleId, $scope, $rules);

            $logOptions = array(
                'attributeName' => 'accessRole',
                'attributeId'   => $thisRole->roleId,
            );

            $this->_helper->log(Zend_Log::INFO, 'Role ' . $thisRole->name . ' was modified', $logOptions);

            $this->_helper->messenger->addSuccess('Role permissions were set successfully');
            
            $this->_helper->redirector->gotoRoute(array('controller' => 'acl', 'action' => 'details', 'roleId' => $thisRole->roleId, 'scope' => $scope), 'ot', true);

        }        

        $this->_helper->pageTitle("ot-acl-details:title", $thisRole->name);

        $this->view->assign(array(
            'inheritRole'     => $inheritRoleName,
            'remoteResources' => $remoteResources,
            'guestHasAccess'  => $this->_helper->hasAccess('index', 'ot_api', $this->_helper->configVar('defaultRole')),
            'defaultRole'     => $defaultRole,
            'role'            => $thisRole->toArray(),
            'resources'       => $resources,
            'scope'           => $this->_getParam('scope', 'application'),
            'children'        => $this->_acl->getChildrenOfRole($thisRole->roleId),
        ));
    }

    /**
     * Add a new role to the ACL
     *
     */
    public function addAction()
    {
        $form = new Ot_Form_Role();

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $data = array(
                    'name'          => $form->getValue('name'),
                    'inheritRoleId' => $form->getValue('inheritRoleId'),
                    'editable'      => 1,
                );

                $role = new Ot_Model_DbTable_Role();
                $roleId = $role->insert($data);

                $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $roleId,
                );

                $this->_helper->log(Zend_Log::INFO, 'Role ' . $data['name'] . ' was added', $logOptions);

                $this->_helper->redirector->gotoRoute(array('controller' => 'acl', 'action' => 'details', 'roleId' => $roleId), 'ot', true);
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
        $roleId = $this->_getParam('roleId', null);

        if (is_null($roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }

        $role = new Ot_Model_DbTable_Role();

        $thisRole = $role->find($roleId);
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }

        if ($thisRole->editable != 1) {
            throw new Ot_Exception_Access('msg-error-unallowedRoleEdit');
        }

        $form = new Ot_Form_Role();
        $form->populate($thisRole->toArray());

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $data = array(
                    'roleId'        => $roleId,
                    'name'          => $form->getValue('name'),
                    'inheritRoleId' => $form->getValue('inheritRoleId'),
                );

                $role->update($data, null);

                $logOptions = array(
                    'attributeName' => 'accessRole',
                    'attributeId'   => $data['roleId'],
                );

                $this->_helper->log(Zend_Log::INFO, 'Role ' . $data['name'] . ' was modified', $logOptions);
    
                $this->_helper->messenger->addSuccess('Role was saved successfully');
            
                $this->_helper->redirector->gotoRoute(array('controller' => 'acl', 'action' => 'details', 'roleId' => $roleId), 'ot', true);
            } else {
                $this->_helper->messenger->addError('msg-error-invalidForm');
            }
        }

        $this->_helper->pageTitle("ot-acl-edit:title");

        $this->view->assign(array(
            'role' => $thisRole,
            'form' => $form,
        ));
    }

    /**
     * Deletes a role from the ACL
     *
     */
    public function deleteAction()
    {
        $roleId = $this->_getParam('roleId', null);

        if (is_null($roleId)) {
            throw new Ot_Exception_Input('msg-error-roleIdNotSet');
        }

        $role = new Ot_Model_DbTable_Role();

        $thisRole = $role->find($roleId);
        if (is_null($thisRole)) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }

        if ($thisRole->editable != 1) {
            throw new Ot_Exception_Access('msg-error-unallowedRoleEdit');
        }

        $availableRoles = $this->_acl->getAvailableRoles();

        if (!isset($availableRoles[$roleId])) {
            throw new Ot_Exception_Data('msg-error-noRole');
        }

        $account = new Ot_Model_DbTable_Account();
        $affectedAccounts = $account->getAccountsForRole($get->roleId);

        $defaultRole = $this->_helper->configVar('defaultRole');

        if (!isset($availableRoles[$defaultRole])) {
            throw new Ot_Exception_Data('msg-error-noDefaultRole');
        }

        if ($defaultRole == $roleId) {
            throw new Ot_Exception_Data('msg-error-deleteDefaultRole');
        }

        $inheritedRoles = array();
        $inheritedRoles = $this->_acl->getChildrenOfRole($roleId);

        if (count($inheritedRoles) > 0) {
            throw new Ot_Exception_Data($this->view->translate('msg-error-dependedRoleCannotDelete', $roleList));
        }

        if ($this->_request->isPost()) {

            $role = new Ot_Model_DbTable_Role();
            $accountRoles = new Ot_Model_DbTable_AccountRoles();

            $dba = $role->getAdapter();
            $dba->beginTransaction();

            try {
                $role->deleteRole($roleId);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }


            // aList is an array of all the affected accountIds
            $aList = array();
            if (count($affectedAccounts) > 0) {
                foreach($affectedAccounts as $a) {
                    $aList[] = $a->accountId;
                }

                if (count($aList) > 0) {

                    // get a list of all the accounts that still have a role after removing one so we can diff()
                    // it to find the accounts that no longer have a role
                    $accountRolesDba = $accountRoles->getAdapter();
                    $where = $accountRolesDba->quoteInto('accountId IN(?)', $aList);

                    $affectedAccountsStillWithRoles = $accountRoles->fetchAll($where);

                    $affectedAccountsStillWithRolesIds = array();
                    foreach ($affectedAccountsStillWithRoles as $a) {
                        $affectedAccountsStillWithRolesIds[] = $a->accountId;
                    }

                    // here's the list of accounts that don't have a role, so we have to add $defaultRole to them.
                    $affectedAccountsWithNoRoles = array_diff($aList, $affectedAccountsStillWithRolesIds);

                    try {
                        foreach ($affectedAccountsWithNoRoles as $a) {
                            $accountRoles->insert(
                                array(
                                    'accountId' => $a,
                                    'roleId'    => $defaultRole,
                                )
                            );
                        }
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }

                }
            }

            $dba->commit();

            $logOptions = array(
                'attributeName' => 'accessRole',
                'attributeId'   => $roleId,
            );

            $this->_helper->log(Zend_Log::INFO, 'Role ' . $thisRole['name'] . ' was deleted', $logOptions);
            
            $this->_helper->messenger->addWarning('Role was deleted successfully');
                
            $this->_helper->redirector->gotoRoute(array('controller' => 'acl'), 'ot', true);
        } else {
            throw new Ot_Exception_Access('You can not access this method directly');
        }
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
                                    && ($acl->isAllowed($inheritRoleId, $resource) || !$acl->isAllowed($inheritRoleId, $resource, $action))) {
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