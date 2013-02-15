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
 * @package    Account_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to set a customized account linked to their user ID.
 *
 * @package    Account_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 *
 */
class Ot_AccountController extends Zend_Controller_Action
{
    /**
     * Authetication adapter
     *
     * @var Ot_Auth_Adapter
     */
    protected $_authAdapter = null;

    /**
     * Array containing user data for the current account being accessed
     *
     * @var unknown_type
     */
    protected $_userData = array();

    /**
     * Runs when the class is initialized.  For the accounts controller, some
     * users are allowed to access others accounts.  For them, we mask as
     * that user to provide the required functionality
     *
     */
    public function init()
    {
        parent::init();

        $get = Zend_Registry::get('getFilter');

        $userData = array();

        $userData['accountId'] = Zend_Auth::getInstance()->getIdentity()->accountId;
        if ($get->accountId && $this->_helper->hasAccess('editAllAccounts')) {
            $userData['accountId'] = $get->accountId;
        }

        $account = new Ot_Model_DbTable_Account();
        $thisAccount = $account->find($userData['accountId']);

        if (is_null($thisAccount)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }

        $userData = array_merge($userData, (array) $thisAccount);


        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter = $authAdapter->find($userData['realm']);
        $a = $adapter;

        $this->_authAdapter = new $a->class;
        $userData['authAdapter'] = array(
           'realm'       => $userData['realm'],
           'name'        => $a->name,
           'description' => $a->description,
        );

        $this->_userData = $userData;
    }

    public function masqueradeAction()
    {

        $this->_helper->pageTitle('Masquerade');

        $this->view->messages = $this->_helper->messenger->getMessages();
        $this->view->masquerading = false;

        $identity = Zend_Auth::getInstance()->getIdentity();

        if (isset($identity->masquerading) && $identity->masquerading) {
            $this->view->masquerading = true;
            $this->view->identity = $identity;
        } else {

            $accountModel = new Ot_Model_DbTable_Account();
            $form = $accountModel->masqueradeForm();

            if ($this->_request->isPost()) {

                if ($form->isValid($_POST)) {

                    $mAccount = $accountModel->getAccount($form->getValue('username'), $form->getValue('realm'));

                    if ($mAccount->accountId == $identity->accountId) {
                        throw new Ot_Exception('You cannot masquerade as yourself.');
                    }

                    if (is_null($mAccount)) {
                        throw new Ot_Exception('The account was not found.');
                    }

                    $mAccount->role = $this->_helper->varReg('newAccountRole');

                    $mAccount->realAccount = $identity;
                    $mAccount->masquerading = true;

                    Zend_Auth::getInstance()->getStorage()->write($mAccount);

                    $this->_helper->messenger->addInfo('You are now masquerading as ' . $mAccount->firstName . ' ' . $mAccount->lastName . ' (' . $mAccount->username . ' in ' . $mAccount->realm . ' realm).');

                    $this->_helper->redirector->gotoRoute(array('action' => 'index', 'controller' => 'index'), 'default', true);

                }
            }

            $this->view->form = $form;
        }
    }

    public function unmasqueradeAction()
    {
    	$this->view->messages = $this->_helper->messenger->getMessages();

        $identity = Zend_Auth::getInstance()->getIdentity();

        if (!$identity->masquerading) {
            throw new Ot_Exception('You are not masquerading!');
        }

        $realIdentity = $identity->realAccount;

        Zend_Auth::getInstance()->clearIdentity();

        $realIdentity->masquerading = false;

        Zend_Auth::getInstance()->getStorage()->write($realIdentity);

        $this->_helper->messenger->addInfo('You are no longer masquerading.');

        $this->_helper->redirector->gotoRoute(array('action' => 'masquerade'), 'account', true);

    }

    /**
     * Default user profile page
     *
     */
    public function indexAction()
    {
        $this->view->acl = array(
            'edit'           => $this->_helper->hasAccess('edit'),
            'delete'         => ($this->_helper->hasAccess('delete')
                && $this->_userData['accountId'] != Zend_Auth::getInstance()->getIdentity()->accountId),
            'changePassword' => $this->_authAdapter->manageLocally()
                && $this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId
                && $this->_helper->hasAccess('change-password'),
            'apiAppAdd'      => $this->_helper->hasAccess('add', 'ot_apiapp'),
            'apiAppDelete'   => $this->_helper->hasAccess('delete', 'ot_apiapp'),
            'apiAppEdit'     => $this->_helper->hasAccess('edit', 'ot_apiapp'),
            'apiDocs'        => $this->_helper->hasAccess('api-docs', 'ot_apiapp'),
            'guestApiAccess' => $this->_helper->hasAccess('index', 'ot_api', $this->_helper->varReg('defaultRole')),
        );


        $this->view->messages = $this->_helper->messenger->getMessages();
        $this->view->userData = $this->_userData;

        $this->_helper->pageTitle(
            'ot-account-index:title',
            array(
                $this->_userData['firstName'],
                $this->_userData['lastName'],
                $this->_userData['username'],
            )
        );

        $loginOptions = Zend_Registry::get('applicationLoginOptions');

        if (isset($loginOptions['accountPlugin'])) {
            $acctPlugin = new $loginOptions['accountPlugin'];
            $attributes = $acctPlugin->get($this->_userData['accountId']);
        }

        $rolesDb = new Ot_Model_DbTable_AccountRoles();
        $where = $rolesDb->getAdapter()->quoteInto('accountId = ?', $this->_userData['accountId']);
        $roleIds = $rolesDb->fetchAll($where)->toArray();

        if (count($roleIds) == 0) {
            throw new Ot_Exception_Data('Role id not found');
        }

        foreach ($roleIds as &$r) {
            $r = $r['roleId'];
        }

        $role = new Ot_Model_DbTable_Role();
        $where = $role->getAdapter()->quoteInto('roleId IN (?)', $roleIds);
        $roles = $role->fetchAll($where)->toArray();

        foreach ($roles as &$r) {
             $r = $r['name'];
        }

        $custom = new Ot_Model_Custom();

        $data = $custom->getData('Ot_Profile', $this->_userData['accountId'], 'none', false);
        foreach ($data as $d) {
            $attributes[$d['attribute']['label']] = $d['value'];
        }

        $apiApp = new Ot_Model_DbTable_ApiApp();

        $apiApps = $apiApp->getAppsForAccount($this->_userData['accountId'], 'access')->toArray();

        $this->view->assign(array(
            'attributes' => $attributes,
            'roles'      => $roles,
            'apiApps'    => $apiApps,
            'tab'        => $this->_getParam('tab', 'account'),
        ));

    }

    /**
     * Display a list of all users in the system.
     *
     */
    public function allAction()
    {

        $this->view->acl = array(
            'add'    => $this->_helper->hasAccess('add'),
            'edit'   => $this->_helper->hasAccess('edit'),
            'delete' => $this->_helper->hasAccess('delete'),
        );

        $this->_helper->pageTitle('ot-account-all:title');
        $this->view->messages = $this->_helper->messenger->getMessages();

        $filterUsername = $this->_getParam('username');
        $filterFirstName = $this->_getParam('firstName');
        $filterLastName = $this->_getParam('lastName');
        $filterRole = $this->_getParam('role', 'any');

        $filterSort = $this->_getParam('sort', 'username');
        $filterDirection = $this->_getParam('direction', 'asc');

        $form = new Ot_Form_UserSearch();
        $form->populate($_GET);

        $account = new Ot_Model_DbTable_Account();
        $accountTbl = $account->info('name');


        $select = new Zend_Db_Table_Select($account);
        $select->from($accountTbl);

        if ($filterUsername != '') {
            $select->where($accountTbl . '.username LIKE ?', '%' . $filterUsername . '%');
        }

        if ($filterFirstName != '') {
            $select->where($accountTbl . '.firstName LIKE ?', '%' . $filterFirstName . '%');
        }

        if ($filterLastName != '') {
            $select->where($accountTbl . '.lastName LIKE ?', '%' . $filterLastName . '%');
        }

        if ($filterRole != '' && $filterRole != 'any') {
            $otRole = new Ot_Model_DbTable_AccountRoles();

            $roleTbl = $otRole->info('name');

            $select->join($roleTbl, $accountTbl . '.accountId = ' . $roleTbl . '.accountId', array());

            $select->where($roleTbl . '.roleId = ?', $filterRole);
            $select->distinct();
        }


        if ($filterSort == 'name') {
            $select->order('firstName ' . $filterDirection);
            $select->order('lastName ' . $filterDirection);
        } else {
            $select->order($filterSort . ' ' . $filterDirection);
        }


        $adapter = new Zend_Paginator_Adapter_DbSelect($select);

        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));


        $aa = new Ot_Model_DbTable_AuthAdapter();

        $adapters = $aa->fetchAll();

        $adapterMap = array();

        foreach ($adapters as $a) {
            $adapterMap[$a->adapterKey] = $a;
        }

        $this->view->assign(array(
            'paginator'     => $paginator,
            'form'          => $form,
            'interface'     => true,
            'sort'          => $filterSort,
            'direction'     => $filterDirection,
            'adapters'      => $adapterMap,
        ));

        /*
        if ($this->_request->isXmlHttpRequest()) {

            $filter = Zend_Registry::get('postFilter');

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender();

            $account = new Ot_Model_DbTable_Account();

            $sortname  = (isset($filter->sortname)) ? $filter->sortname : 'username';
            $sortorder = (isset($filter->sortorder)) ? $filter->sortorder : 'asc';
            $rp        = (isset($filter->rp)) ? $filter->rp : 15;
            $page      = ((isset($filter->page)) ? $filter->page : 1) - 1;
            $qtype     = (isset($filter->query) && !empty($filter->query)) ? $filter->qtype : null;
            $query     = (isset($filter->query) && !empty($filter->query)) ? $filter->query : null;

            $acl = Zend_Registry::get('acl');
            $roles = $acl->getAvailableRoles();

            $where = null;

            if (!is_null($query)) {
                if ($qtype == 'role') {
                    // find what role Id corresponds to the text string that was entered
                    foreach ($roles as $r) {
                        if ($query == $r['name']) {
                            $query = $r['roleId'];
                            break;
                        }
                    }
                }

                $where = $account->getAdapter()->quoteInto($qtype . ' = ?', $query);
            }

            // if they're searching by a role, you can't use the $where, since role no longer exists in the account table
            if($qtype == 'role') {
                $accounts = $account->getAccountsForRole($query, $sortname . ' ' . $sortorder, $rp, $page * $rp);
                $totals = $account->getAccountsForRole($query);
            } else{
                $accounts = $account->fetchAll($where, $sortname . ' ' . $sortorder, $rp, $page * $rp);
                $totals = $account->fetchAll($where);
            }

            $response = array(
                'page' => $page + 1,
                'total' => count($totals),
                'rows'  => array(),
            );

            $otAuth = new Ot_Model_DbTable_AuthAdapter();
            $adapters = $otAuth->fetchAll();

            $realmMap = array();
            foreach ($adapters as $a) {
                $realmMap[$a->adapterKey] = $a->name;
            }

            // TODO: fix bug on account/all page. Search results are not displaying correctly
            if(count($accounts) > 0) {
                foreach ($accounts as $a) {

                    $roleList = array();

                    foreach ($a->role as $r) {
                        $roleList[] = $roles[$r]['name'];
                    }

                    $row = array(
                        'id'   => $a->accountId,
                        'cell' => array(
                            $a->username,
                            $a->firstName,
                            $a->lastName,
                            $realmMap[$a->realm],
                            implode(', ', $roleList)
                        ),
                    );

                    $response['rows'][] = $row;
                }
            }

            echo Zend_Json::encode($response);
            return;
        }
         */
    }

    /**
     * Adds a user to the system
     *
     */
    public function addAction()
    {
    	$this->view->messages = $this->_helper->messenger->getMessages();

        $account = new Ot_Model_DbTable_Account();
        $loginOptions = Zend_Registry::get('applicationLoginOptions');

        $defaultRole = $this->_helper->varReg('defaultRole');
        $values = array('role' => $defaultRole);

        $form = $account->form($values);

        $acl = Zend_Registry::get('acl');

        $permissions = $acl->getResources($defaultRole);

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $password = $account->generatePassword();

                $accountData = array(
                    'username'     => $form->getValue('username'),
                    'password'     => md5($password),
                    'realm'        => $form->getValue('realm'),
                    'firstName'    => $form->getValue('firstName'),
                    'lastName'     => $form->getValue('lastName'),
                    'emailAddress' => $form->getValue('emailAddress'),
                    'timezone'     => $form->getValue('timezone'),
                    'role'         => (array)$form->getValue('roleSelect'),
                );
                if(!isset($accountData['role']) || count($accountData['role']) < 1) {
                    $accountData['role'] = $this->_helper->varReg('defaultRole');
                }

                $dba = Zend_Db_Table::getDefaultAdapter();
                $dba->beginTransaction();

                // Account table
                $thisAccount = $account->getAccount($accountData['username'], $accountData['realm']);

                if (is_null($thisAccount)) {
                    try {
                        $accountData['accountId'] = $account->insert($accountData);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }
                } else {
                    $this->_helper->messenger->addError('msg-error-accountTaken');
                }


                $accountData['password'] = $password;

                // Account plugin
                if ($this->_helper->messenger->count('error') == 0 && isset($loginOptions['accountPlugin'])) {
                    $acctPlugin = new $loginOptions['accountPlugin'];

                    $subform = $acctPlugin->addSubForm();

                    $data = array('accountId' => $accountData['accountId']);

                    foreach ($subform->getElements() as $e) {
                        $data[$e->getName()] = $form->getValue($e->getName());
                    }

                    try {
                        $acctPlugin->addProcess($data);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }
                }

                // Custom attributes
                if ($this->_helper->messenger->count('error') == 0) {

                    $custom = new Ot_Model_Custom();

                    $attributes = $custom->getAttributesForObject('Ot_Profile');

                    $data = array();
                    foreach ($attributes as $a) {
                        $data[$a['attributeId']] = $form->getValue('custom_' . $a['attributeId']);
                    }

                    try {
                        $custom->saveData('Ot_Profile', $accountData['accountId'], $data);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }
                }

                if ($this->_helper->messenger->count('error') == 0) {
                    $dba->commit();

                    $this->_helper->messenger->addSuccess('msg-info-accountCreated');

                    $td = new Ot_Trigger_Dispatcher();
                    $td->setVariables($accountData);


                    $role = new Ot_Model_DbTable_Role();

                    $roles = array();
                    foreach ($accountData['role'] as $r) {
                        $roles[] = $role->find($r)->name;
                    }

                    $otAuthAdapter = new Ot_Model_DbTable_AuthAdapter();

                    $thisAdapter = $otAuthAdapter->find($accountData['realm']);

                    $td->role = implode(',', $roles);
                    $td->loginMethod = $thisAdapter->name;

                    $authAdapter = new $thisAdapter->class;

                    if ($authAdapter->manageLocally()) {
                        $this->_helper->messenger->addSuccess('msg-info-accountPasswordCreated');

                        $td->dispatch('Admin_Account_Create_Password');
                    } else {
                        $td->dispatch('Admin_Account_Create_NoPassword');
                    }

                    $logOptions = array(
                        'attributeName' => 'accountId',
                        'attributeId'   => $accountData['accountId'],
                    );

                    $this->_helper->log(Zend_Log::INFO, 'Account was added', $logOptions);

                    $this->_helper->redirector->gotoRoute(array('action' => 'all'), 'account', true);
                }
            } else {
                $this->_helper->messenger->addError('msg-error-invalidForm');
            }
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.tooltip.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/account/permissionsTable.js');
        $this->_helper->pageTitle('ot-account-add:title');

        $this->view->form = $form;
        $this->view->permissions = $permissions;
        $this->view->permissionList = Zend_Json::encode($permissions);

    }

    /**
     * Imports users in bulk
     *
     */
    public function importAction()
    {
        $account = new Ot_Model_DbTable_Account();
        $form = $account->importForm();

        if ($this->_request->isPost()) {

            if ($form->isValid($_POST)) {

                $cleanImport = preg_replace("/[^A-Z0-9,-]/i", "", $form->getValue('text'));
                $userList = explode(",", $cleanImport);

                $success = array();
                $failure = array();

                foreach ($userList as $userId) {

                    $userId = trim($userId);

                    if (empty($userId)) {
                        continue;
                    }

                    try {
                        $account->createNewUserForUnityId($userId, $form->getValue('newRoleId'));
                        $success[] = $userId;
                    } catch (Exception $e) {
                        $failure[] = $userId . ' (' . $e->getMessage() . ')';
                    }
                }

                if (count($success)) {
                    $this->_helper->messenger->addSuccess('Successfully imported account(s) for ' . implode(', ', $success) . '.');
                }

                if (count($failure)) {
                    $this->_helper->messenger->addError('Failed to import account(s) for ' . implode(', ', $failure) . '.');
                }

                $this->_helper->redirector->setPrependBase('')
                     ->gotoUrl($this->view->url(array('module' => 'ot', 'controller' => 'account', 'action' => 'import'), 'default', true));
            } else {
                $this->_helper->messenger->addError('There was an error processing the form.');
            }

        }

        $this->view->form = $form;
        $this->_helper->pageTitle('Batch Create Accounts from Unity ID List');
        $this->view->messages = $this->_helper->messenger->getMessages();
    }

    /**
     * Edits an existing user
     *
     */
    public function editAction()
    {
        $account = new Ot_Model_DbTable_Account();

        $req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');

        $loginOptions = Zend_Registry::get('applicationLoginOptions');

        $form = $account->form($this->_userData);

        $rolesDb = new Ot_Model_DbTable_AccountRoles();

        $where = $rolesDb->getAdapter()->quoteInto('accountId = ?', $this->_userData['accountId']);

        $result = $rolesDb->fetchAll($where);

        if(count($result) < 1) {
            throw new Ot_Exception_Data('No roles associated with this account');
        }

        $acl = Zend_Registry::get('acl');

        $resources = array();
        foreach ($result as $r) {
            $resources[] = $acl->getResources($r->roleId);
        }

        $permissions = $this->mergeResources($resources);

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $dba = Zend_Db_Table::getDefaultAdapter();

                $data = array(
                    'accountId'    => $this->_userData['accountId'],
                    'firstName'    => $form->getValue('firstName'),
                    'lastName'     => $form->getValue('lastName'),
                    'emailAddress' => $form->getValue('emailAddress'),
                    'timezone'     => $form->getValue('timezone'),
                );

                if ($this->_userData['accountId']
                    != Zend_Auth::getInstance()->getIdentity()->accountId) {
                    $data['realm']    = $form->getValue('realm');
                    $data['role']     = (array)$form->getValue('roleSelect');

                    if(!isset($data['role']) || count($data['role']) < 1) {
                        $data['role'] = $this->_helper->varReg('defaultRole');
                    }

                    $data['username'] = $form->getValue('username');
                }



                $account = new Ot_Model_DbTable_Account();

                $thisAccount = $account->getAccount($data['username'], $data['realm']);

                if (!is_null($thisAccount) && $thisAccount->accountId != $data['accountId']) {
                    $this->_helper->messenger->addError('msg-error-accountTaken');
                } else {

                    $dba->beginTransaction();

                    try {
                        $account->update($data, null);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }

                    if (isset($loginOptions['accountPlugin'])) {
                        $acctPlugin = new $loginOptions['accountPlugin']();

                        $subform = $acctPlugin->editSubForm($this->_userData['accountId']);

                        $data = array('accountId' => $this->_userData['accountId']);

                        foreach ($subform->getElements() as $e) {
                            $data[$e->getName()] = $form->getValue($e->getName());
                        }

                        try {
                            $acctPlugin->editProcess($data);
                        } catch (Exception $e) {
                            $dba->rollback();
                            throw $e;
                        }
                    }

                    $custom = new Ot_Model_Custom();

                    $attributes = $custom->getAttributesForObject('Ot_Profile');

                    $data = array();
                    foreach ($attributes as $a) {
                        $data[$a['attributeId']] = $form->getValue('custom_' . $a['attributeId']);
                    }

                    try {
                        $custom->saveData('Ot_Profile', $this->_userData['accountId'], $data);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }

                    $dba->commit();

                    $loggerOptions = array(
                        'attributeName' => 'accountId',
                        'attributeId'   => $this->_userData['accountId'],
                    );

                    $this->_helper->log(Zend_Log::INFO, 'Account was modified.', $loggerOptions);

                    if (isset($req->uri) && $req->uri != '') {
                        $uri = $req->uri;
                        $req->unsetAll();
                        $this->_helper->redirector->gotoUrl($uri);
                    } else {
                        $this->_helper->messenger->addSuccess('msg-info-accountUpdated');
                        $this->_helper->redirector->gotoRoute(
                            array(
                                'accountId' => $this->_userData['accountId']
                            ),
                            'account',
                            true
                        );
                    }
                }
            } else {
                $this->_helper->messenger->addError('msg-error-invalidForm');
            }
        }

        if (isset($req->uri) && $req->uri != '') {
            $this->_helper->messenger->addError('msg-info-requiredDataBeforeContinuing');
        }

        if ($this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId) {
            $this->_helper->messenger->addInfo('msg-info-editAccountSelf');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.tooltip.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/account/permissionsTable.js');

        $this->view->form = $form;
        $this->view->permissions = $permissions;
        $this->view->permissionList = Zend_Json::encode($permissions);
        $this->_helper->pageTitle('ot-account-edit:title');

        $this->view->acl = array(
            'edit-permission' => $this->_helper->hasAccess('edit', 'ot_acl'),
        );
    }

    /**
     * Deletes a user
     *
     */
    public function deleteAction()
    {
        if ($this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId) {
            throw new Ot_Exception_Access('msg-error-accountAccessDelete');
        }

        $form = Ot_Form_Template::delete('deleteUser');

        if ($this->_request->isPost() && $form->isValid($_POST)) {

            $dba = Zend_Db_Table::getDefaultAdapter();
            $dba->beginTransaction();

            $account = new Ot_Model_DbTable_Account();

            $where = $account->getAdapter()->quoteInto('accountId = ?', $this->_userData['accountId']);

            try {
                $account->delete($where);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            $loginOptions = Zend_Registry::get('applicationLoginOptions');

            if (isset($loginOptions['accountPlugin'])) {
                $acctPlugin = new $loginOptions['accountPlugin']();

                try {
                    $acctPlugin->deleteProcess($this->_userData['accountId']);
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
            }

            $custom = new Ot_Model_Custom();

            try {
                $custom->deleteData('Ot_Profile', $this->_userData['accountId']);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            $dba->commit();

            $loggerOptions = array(
                'attributeName' => 'accountId',
                'attributeId'   => $this->_userData['accountId'],
            );

            $this->_helper->log(Zend_Log::INFO, 'ot-account-delete:accountDeleted', $loggerOptions);

            $this->_helper->messenger->addSuccess('msg-info-accountDeleted');

            $this->_helper->redirector->gotoRoute(array('action' => 'all'), 'account', true);
        }

        $this->view->userData = $this->_userData;
        $this->_helper->pageTitle('ot-account-delete:title');
        $this->view->form = $form;
    }

    /**
     * Allows a user to revoke their connection to a remote application
     *
     */
        public function revokeConnectionAction()
        {
                $this->_helper->pageTitle('ot-account-revokeConnection:title');

                $get = Zend_Registry::get('getFilter');

                if (!isset($get->consumerId)) {
                    throw new Ot_Exception_Input('ot-account-revokeConnection:consumerIdNotSet');
                }

                $consumer = new Ot_Model_DbTable_OauthServerConsumer();

                $thisConsumer = $consumer->find($get->consumerId);
                if (is_null($thisConsumer)) {
                    throw new Ot_Exception_Data('ot-account-revokeConnection:consumerIdNotExists');
                }

                $st = new Ot_Model_DbTable_OauthServerToken();

                $existingAccessToken = $st->getTokenByAccountAndConsumer(
                    $this->_userData['accountId'], $thisConsumer->consumerId,
                    'access'
                );
                if (is_null($existingAccessToken)) {
                    throw new Ot_Exception_Data('ot-account-revokeConnection:noAccessToken');
                }

                $form = Ot_Form_Template::delete('revokeAccess', 'Revoke Access', 'Cancel');

                if ($this->_request->isPost() && $form->isValid($_POST)) {
                    $st->removeToken($existingAccessToken->token);

                    $this->_helper->flashMessenge->addInfo(
                        //'Token has been removed. ' . $thisConsumer->name . ' no longer has access to your account.'
                        $this->view->translate('ot-account-revokeConnection:tokenremoved', array($thisConsumer->name))
                    );

                    $this->_helper->redirector->gotoRoute(array(), 'account', true);
                }

                $this->view->form = $form;
                $this->view->consumer = $thisConsumer;
        }

    /**
     * allows a user to change their password
     *
     */
    public function changePasswordAction()
    {
    	$this->view->messages = $this->_helper->messenger->getCurrentMessages();
        $identity = Zend_Auth::getInstance()->getIdentity();

        $account = new Ot_Model_DbTable_Account();

        $thisAccount = $account->getAccount($identity->username, $identity->realm);
        if (is_null($thisAccount)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }

        $otAuthAdapter = new Ot_Model_DbTable_AuthAdapter();
        $thisAdapter = $otAuthAdapter->find($thisAccount->realm);
        $auth = new $thisAdapter->class();

        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('msg-error-authAdapterSupport');
        }

        $form = new Zend_Form();
        $form->setAttrib('id', 'changePassword')->setDecorators(
            array(
                'FormElements',
                array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                'Form',
            )
        );

        $oldPassword = $form->createElement(
            'password',
            'oldPassword',
            array('label' => 'ot-account-changePassword:form:oldPassword')
        );
        $oldPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(5, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags');

        $newPassword = $form->createElement(
            'password',
            'newPassword',
            array('label' => 'ot-account-changePassword:form:newPassword')
        );
        $newPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(5, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags');

        $newPasswordConf = $form->createElement(
            'password',
            'newPasswordConf',
            array('label' => 'ot-account-changePassword:form:newPasswordConf')
        );
        $newPasswordConf->setRequired(true)
                        ->addValidator('StringLength', false, array(5, 20))
                        ->addFilter('StringTrim')
                        ->addFilter('StripTags');

        $submit = $form->createElement(
            'submit',
            'changeButton',
            array('label' => 'ot-account-changePassword:form:submit')
        );
        $submit->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));

        $form->addElements(array($oldPassword, $newPassword, $newPasswordConf))->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                array('Label', array('tag' => 'span')),
            )
        )->addElements(array($submit, $cancel));

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                if ($form->getValue('newPassword')
                    != $form->getValue('newPasswordConf')) {
                    $this->_helper->messenger->addError('msg-error-passwordMismatch');
                }

                if (md5($form->getValue('oldPassword'))
                    != $thisAccount->password) {
                    $this->_helper->messenger->addError('msg-error-passwordInvalidOriginal');
                }

                if ($this->_helper->messenger->count('error') == 0) {
                    $data = array(
                        'accountId' => $thisAccount->accountId,
                        'password'  => md5($form->getValue('newPassword'))
                    );

                    $account->update($data, null);

                    $this->_helper->messenger->addSuccess('msg-info-passwordChanged');
                    $this->_helper->messenger->addSuccess('tesingggg');

                    $loggerOptions = array(
                        'attributeName' => 'accountId',
                        'attributeId'   => $thisAccount->accountId,
                    );

                    $this->_helper->log(Zend_Log::INFO, 'User changed Password', $loggerOptions);

                    $this->_helper->redirector->gotoRoute(array(), 'account', true);
                }
            } else {
                $this->_helper->messenger->addError('msg-error-invalidForm');
            }
        }

        $this->view->headScript()->appendFile(
            $this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.passStrength.js'
        );

        $this->_helper->pageTitle('ot-account-changePassword:title');
        $this->view->form  = $form;
    }

    /**
     * Change user roles in bulk
     *
     */
    public function changeRolesAction()
    {

        $account = new Ot_Model_DbTable_Account();
        $form = $account->changeRoleForm();


        if ($this->_request->isPost()) {

            if ($form->isValid($_POST)) {

                $cleanImport = preg_replace("[^A-Za-z0-9,-]", "", $form->getValue('text'));
                $user = explode(",", $cleanImport);

                $success = array();
                $failure = array();

                foreach ($user as $userId) {

                    $userId = trim($userId);

                    if (empty($userId)) {
                        continue;
                    }

                    try {
                        $account->changeAccountRoleForUnityId($userId, $form->getValue('newRoleId'));
                        $success[] = $userId;
                    } catch (Exception $e) {
                        $failure[] = $userId . ' (' . $e->getMessage() . ')';
                    }
                }

                if (count($success)) {
                    $this->_helper->messenger->addSuccess('Successfully changed role(s) for ' . implode(', ', $success) . '.');
                }

                if (count($failure)) {
                    $this->_helper->messenger->addError('Failed to change role(s) for ' . implode(', ', $failure) . '.');
                }

                $this->_helper->redirector->setPrependBase('')
                     ->gotoUrl($this->view->url(array('module' => 'ot', 'controller' => 'account', 'action' => 'change-roles'), 'default', true));
            } else {
                $this->_helper->messenger->addError('There was an error processing the form.');
            }

        }

        $this->view->form = $form;
        $this->_helper->pageTitle('Change Roles for Unity ID List');
        $this->view->messages = $this->_helper->messenger->getMessages();

    }

    /**
     * Allows a user to edit all user accounts in the system
     *
     */
    public function editAllAccountsAction()
    {
    }

    private function mergeResources($resources) {
        $permissions = array_pop($resources);

        foreach ($resources as $resource) {
            foreach ($resource as $module => $controllers) {
                foreach($controllers as $controller => $parts) {
                    foreach($parts as $part => $rules) {
                        if($part != 'description') {
                            if(isset($rules['access'])) {
                                $permissions[$module][$controller][$part]['access'] = $rules['access'] || $permissions[$module][$controller][$part]['access'];
                            } else {
                                foreach($rules as $rule => $access) {
                                    $permissions[$module][$controller][$part][$rule]['access'] = $access['access'] || $permissions[$module][$controller][$part][$rule]['access'];
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($permissions as &$permission) {
            foreach ($permission as &$c) {
                $c['someAccess'] = false;
                foreach ($c['part'] as $p) {
                    if ($p['access']) {
                        $c['someAccess'] = true;
                    }
                }
            }
        }
        return $permissions;
    }

    /**
     * Compiles all the permissions for a given set of roles
     *
     * @param unknown_type $roles
     */
    public function getPermissionsAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = Zend_Registry::get('getFilter');

        $roles = $get->roles;

        if (!isset($roles) || count($roles) < 1) {
            $roles = array($this->_helper->varReg('defaultRole'));
        }

        $acl = Zend_Registry::get('acl');

        $resources = array();
        foreach($roles as $role) {
            $resources[] = $acl->getResources($role);
        }

        $permissions = $this->mergeResources($resources);

        echo Zend_Json_Encoder::encode($permissions);
        return;

    }
}
