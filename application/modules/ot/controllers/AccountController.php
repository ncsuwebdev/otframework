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

        $config = Zend_Registry::get('config');
        $get = Zend_Registry::get('getFilter');

        $userData = array();

        $userData['accountId'] = Zend_Auth::getInstance()->getIdentity()->accountId;
        if ($get->accountId && $this->_helper->hasAccess('editAllAccounts')) {
            $userData['accountId'] = $get->accountId;
        }

        $account = new Ot_Account();
        $thisAccount = $account->find($userData['accountId']);

        if (is_null($thisAccount)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }

        $userData = array_merge($userData, (array) $thisAccount);


        $authAdapter = new Ot_Auth_Adapter();
        $adapter = $authAdapter->find($userData['realm']);
        $a = $adapter;

        //$a = $config->app->authentication->{$userData['realm']};
        $this->_authAdapter = new $a->class;
        $userData['authAdapter'] = array(
           'realm'       => $userData['realm'],
           'name'        => $a->name,
           'description' => $a->description,
        );

        $this->_userData = $userData;
    }

    /**
     * Default user profile page
     *
     */
    public function indexAction()
    {
        $config = Zend_Registry::get('config');

        $this->view->acl = array(
            'edit'            => $this->_helper->hasAccess('edit'),
            'delete'          => ($this->_helper->hasAccess('delete')
                && $this->_userData['accountId'] != Zend_Auth::getInstance()->getIdentity()->accountId),
            'changePassword'  => $this->_authAdapter->manageLocally()
                && $this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId
                && $this->_helper->hasAccess('change-password'),
            'grantAccess'     => ($this->_helper->hasAccess('index', 'ot_oauthclient')
                && $this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId),
            'revokeAccess'    => ($this->_helper->hasAccess('revoke', 'ot_oauthserver')
                && $this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId),
            'oauth'           => $this->_helper->hasAccess('index', 'ot_oauth'),
            'apiDocs'         => $this->_helper->hasAccess('index', 'ot_api')
        );

        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->userData = $this->_userData;

        $this->_helper->pageTitle(
            'ot-account-index:title',
            array(
                $this->_userData['firstName'],
                $this->_userData['lastName'],
                $this->_userData['username'],
            )
        );

        if (isset($config->app->accountPlugin)) {
            $acctPlugin = new $config->app->accountPlugin;
            $attributes = $acctPlugin->get($this->_userData['accountId']);
        }

        $rolesDb = new Ot_Account_Roles();
        $where = $rolesDb->getAdapter()->quoteInto('accountId = ?', $this->_userData['accountId']);
        $roleIds = $rolesDb->fetchAll($where)->toArray();

        if (count($roleIds) == 0) {
            throw new Ot_Exception_Data('Role id not found');
        }

        foreach ($roleIds as &$r) {
            $r = $r['roleId'];
        }

        $role = new Ot_Role();
        $where = $role->getAdapter()->quoteInto('roleId IN (?)', $roleIds);
        $roles = $role->fetchAll($where)->toArray();

        foreach ($roles as &$r) {
             $r = $r['name'];
        }

        $this->view->roles = $roles;
        $custom = new Ot_Custom();

        $data = $custom->getData('Ot_Profile', $this->_userData['accountId'], 'none', false);
        foreach ($data as $d) {
            $attributes[$d['attribute']['label']] = $d['value'];
        }

        $this->view->attributes = $attributes;

        $st = new Ot_Oauth_Server_Token();
        $consumer = new Ot_Oauth_Server_Consumer();

        $tokens = $st->getTokensForAccount($this->_userData['accountId'], 'access')->toArray();

        $consumerIds = array();
        foreach ($tokens as $t) {
            $consumerIds[] = $t['consumerId'];
        }

        if (count($consumerIds) != 0) {
            $where = $consumer->getAdapter()->quoteInto('consumerId IN (?)', $consumerIds);
            $consumers = $consumer->fetchAll($where)->toArray();

            $consumerMap = array();
            foreach ($consumers as $c) {
                $consumerMap[$c['consumerId']] = $c;
            }

            foreach ($tokens as &$t) {
                $t['consumer'] = $consumerMap[$t['consumerId']];
            }
            unset($t);
        }

        $this->view->accessTokens = $tokens;

        $config = Zend_Registry::get('config');

        $consumers = array();
        if ($config->app->oauth->consumers instanceof Zend_Config) {

            $clientToken = new Ot_Oauth_Client_Token();

            $accessTokens = $clientToken->getTokensForAccount($this->_userData['accountId'], 'access');

            $authorized = array();
            foreach ($accessTokens as $a) {
                $authorized[] = $a->consumerId;
            }

            foreach ($config->app->oauth->consumers as $key => $value) {
                $data = $value->toArray();
                $data['consumerId'] = $key;
                $data['authorized'] = (in_array($key, $authorized));
                $consumers[] = $data;
            }
        }

        $this->view->consumers = $consumers;

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
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view
             ->headScript()
             ->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.flexigrid.pack.js');
        $this->view
             ->headLink()
             ->appendStylesheet($this->view->baseUrl() . '/public/css/ot/jquery.plugin.flexigrid.css');

        if ($this->_request->isXmlHttpRequest()) {

            $filter = Zend_Registry::get('postFilter');

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender();

            $account = new Ot_Account();

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
            } else{
                $accounts = $account->fetchAll($where, $sortname . ' ' . $sortorder, $rp, $page * $rp);
            }
            $response = array(
                'page' => $page + 1,
                'total' => count($accounts),
                'rows'  => array(),
            );

            $config = Zend_Registry::get('config');

            $otAuth = new Ot_Auth_Adapter();
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
    }

    /**
     * Adds a user to the system
     *
     */
    public function addAction()
    {
        $account = new Ot_Account();
        $config  = Zend_Registry::get('config');

        $defaultRole = $config->user->defaultRole->val;
        $values = array('role' => $defaultRole);

        $form = $account->form($values);

        $messages = array();

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
                    $accountData['role'] = (array)$config->user->defaultRole->val;
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
                    $messages[] = 'msg-error-accountTaken';
                }


                $accountData['password'] = $password;

                // Account plugin
                if (count($messages) == 0 && isset($config->accountPlugin)) {
                    $acctPlugin = new $config->app->accountPlugin;

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
                if (count($messages) == 0) {

                    $custom = new Ot_Custom();

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

                if (count($messages) == 0) {
                    $dba->commit();

                    $this->_helper->flashMessenger->addMessage('msg-info-accountCreated');

                    $trigger = new Ot_Trigger();
                    $trigger->setVariables($accountData);


                    $role = new Ot_Role();

                    $roles = array();
                    foreach ($accountData['role'] as $r) {
                        $roles[] = $role->find($r)->name;
                    }

                    $otAuthAdapter = new Ot_Auth_Adapter();

                    $thisAdapter = $otAuthAdapter->find($accountData['realm']);

                    $trigger->role = implode(',', $roles);
                    $trigger->loginMethod = $thisAdapter->name;

                    $authAdapter = new $thisAdapter->class;

                    if ($authAdapter->manageLocally()) {
                        $this->_helper->flashMessenger->addMessage('msg-info-accountPasswordCreated');

                        $trigger->dispatch('Admin_Account_Create_Password');
                    } else {
                        $trigger->dispatch('Admin_Account_Create_NoPassword');
                    }

                    $logOptions = array(
                        'attributeName' => 'accountId',
                        'attributeId'   => $accountData['accountId'],
                    );

                    $this->_helper->log(Zend_Log::INFO, 'Account was added', $logOptions);

                    $this->_helper->redirector->gotoRoute(array('action' => 'all'), 'account', true);
                }
            } else {
                $messages[] = 'msg-error-invalidForm';
            }
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.tooltip.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/account/permissionsTable.js');
        $this->_helper->pageTitle('ot-account-add:title');

        $this->view->messages = $messages;
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
        
        $account = new Ot_Account();
        $form = $account->importForm();
        
        $messages = array();
        
        if ($this->_request->isPost()) {
            
            if ($form->isValid($_POST)) {
                $cleanImport = ereg_replace("[^A-Za-z0-9,-]", "", $form->getValue('text'));
                $user = explode(",", $cleanImport);
                
                $success = array();
                $failure = array();
                
                foreach ($user as $userId) {
                    if ($account->createNewUserForUnityId($userId, $form->getValue('newRoleId'))) {
                        $success[] = $userId;
                    } else {
                        $failure[] = $userId;
                    }
                }
                
                if (count($success)) {
                    $this->_helper->flashMessenger->addMessage('Successfully imported account(s) for ' . implode(', ', $success) . '.');
                }
                
                if (count($failure)) {
                    $this->_helper->flashMessenger->addMessage('Failed to import account(s) for ' . implode(', ', $failure) . '.');
                }

                $this->_helper->redirector->setPrependBase('')
                     ->gotoUrl($this->view->url(array('module' => 'ot', 'controller' => 'account', 'action' => 'import'), 'default', true));
            } else {
                $messages[] = 'There was an error processing the form.';
            }
        
        }
        
        $this->view->form = $form;
        $this->_helper->pageTitle('Batch Create Accounts from Unity ID List');
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * Edits an existing user
     *
     */
    public function editAction()
    {
        $account = new Ot_Account();

        $req = new Zend_Session_Namespace(
            Zend_Registry::get('siteUrl') . '_request');

        $config = Zend_Registry::get('config');

        $form = $account->form($this->_userData);

        $rolesDb = new Ot_Account_Roles();

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

        $messages = array();

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
                        $data['role'] = $config->user->defaultRole->val;
                    }

                    $data['username'] = $form->getValue('username');
                }



                $account = new Ot_Account();

                $thisAccount = $account->getAccount($data['username'], $data['realm']);

                if (!is_null($thisAccount) && $thisAccount->accountId != $data['accountId']) {
                    $messages[] = 'msg-error-accountTaken';
                } else {

                    $dba->beginTransaction();

                    try {
                        $account->update($data, null);
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }

                    if (isset($config->app->accountPlugin)) {
                        $acctPlugin = new $config->app->accountPlugin();

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

                    $custom = new Ot_Custom();

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

                    $this->_helper->log(Zend_Log::INFO, 'Account was modified.', loggerOptions);

                    if (isset($req->uri) && $req->uri != '') {
                        $uri = $req->uri;
                        $req->unsetAll();
                        $this->_helper->redirector->gotoUrl($uri);
                    } else {
                        $this->_helper->flashMessenger->addMessage('msg-info-accountUpdated');
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
                $messages[] = 'msg-error-invalidForm';
            }
        }

        if (isset($req->uri) && $req->uri != '') {
            $messages[] = 'msg-info-requiredDataBeforeContinuing';
        }

        if ($this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId) {
            $messages[] = 'msg-info-editAccountSelf';
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ot/jquery.plugin.tipsy.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.tipsy.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.tooltip.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/account/permissionsTable.js');
        $this->view->messages = $messages;
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

            $account = new Ot_Account();

            $where = $account->getAdapter()->quoteInto('accountId = ?', $this->_userData['accountId']);

            try {
                $account->delete($where);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            $config = Zend_Registry::get('config');

            if (isset($config->app->accountPlugin)) {
                $acctPlugin = new $config->app->accountPlugin();

                try {
                    $acctPlugin->deleteProcess($this->_userData['accountId']);
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
            }

            $custom = new Ot_Custom();

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

            $this->_helper->flashMessenger->addMessage('msg-info-accountDeleted');

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

                $consumer = new Ot_Oauth_Server_Consumer();

                $thisConsumer = $consumer->find($get->consumerId);
                if (is_null($thisConsumer)) {
                    throw new Ot_Exception_Data('ot-account-revokeConnection:consumerIdNotExists');
                }

                $st = new Ot_Oauth_Server_Token();

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

                    $this->_helper->flashMessenge->addMessage(
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
        $identity = Zend_Auth::getInstance()->getIdentity();

        $account = new Ot_Account();

        $thisAccount = $account->getAccount($identity->username, $identity->realm);
        if (is_null($thisAccount)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }

        $otAuthAdapter = new Ot_Auth_Adapter();
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

        $messages = array();
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                if ($form->getValue('newPassword')
                    != $form->getValue('newPasswordConf')) {
                    $messages[] = 'msg-error-passwordMismatch';
                }

                if (md5($form->getValue('oldPassword'))
                    != $thisAccount->password) {
                    $messages[] = 'msg-error-passwordInvalidOriginal';
                }

                if (count($messages) == 0) {
                    $data = array(
                        'accountId' => $thisAccount->accountId,
                        'password'  => md5($form->getValue('newPassword'))
                    );

                    $account->update($data, null);

                    $this->_helper->flashMessenger->addMessage('msg-info-passwordChanged');

                    $loggerOptions = array(
                        'attributeName' => 'accountId',
                        'attributeId'   => $thisAccount->accountId,
                    );

                    $this->_helper->log(Zend_Log::INFO, 'User changed Password', $loggerOptions);

                    $this->_helper->redirector->gotoRoute(array(), 'account', true);
                }
            } else {
                $messages[] = 'msg-error-invalidForm';
            }
        }

        $this->view->headScript()->appendFile(
            $this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.passStrength.js'
        );
        $this->view->messages = $messages;
        $this->_helper->pageTitle('ot-account-changePassword:title');
        $this->view->form  = $form;
    }

    /**
     * Allows a user to change their role and others
     *
     */
    public function changeUserRoleAction()
    {
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
            $config = Zend_Registry::get('config');
            $roles = array($config->user->defaultRole->val);
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