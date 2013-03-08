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
 * @package    Login_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to log in and log out of the application, as well as signup
 * for new accounts and reset passwords.
 *
 * @package    Login_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 *
 */
class Ot_LoginController extends Zend_Controller_Action
{

    /**
     * Action when going to the main login page
     *
     */
    public function indexAction()
    {
        $messages = array();

        $this->_helper->pageTitle('ot-login-index:title');

        $req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');
        //var_dump($req->requestUri, $req->uri, $req->requestedFromUrl);exit;

        $loginOptions = Zend_Registry::get('applicationLoginOptions');
        $registry = new Ot_Config_Register();

        $authRealm = new Zend_Session_Namespace('authRealm');
        $authRealm->setExpirationHops(1);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->view->messages = $messages;
            $this->view->alreadyLoggedIn = true;
            $this->view->identity = Zend_Auth::getInstance()->getIdentity();
            return;
        }

        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapters = $authAdapter->getEnabledAdapters();

        if (!$adapters || $adapters->count() == 0) {
            throw new Ot_Exception_Data('ot-login-index:noAdaptersEnabled');
        }

        $loginForms = array();

        $realm = 'local'; //set a default value for $realm, since it's required

        foreach ($adapters as $adapter) {
            $form = new Zend_Form();
            $form->setAttrib('id', $adapter->class)->setDecorators(
                array(
                    'FormElements',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                    'Form',
                )
            )->setAction($this->view->url(array(), 'login', true));

            $a = new $adapter->class;

            if (!$a->autoLogin()) {

                // Create and configure username element:
                $username = $form->createElement('text', 'username', array('label' => 'ot-login-form:username'));
                $username->setRequired(true)->addFilter('StringTrim');

                // Create and configure password element:
                $password = $form->createElement('password', 'password', array('label' => 'ot-login-index:password'));
                $password->addFilter('StringTrim')->setRequired(true);

                $form->addElements(array($username, $password));
            }

            $form->setElementDecorators(
                array(
                    'ViewHelper',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                    array('Label', array('tag' => 'span')),
                )
            );

            $loginButton = $form->createElement('submit', 'loginButton', array('label' => 'ot-login-index:login'));
            $loginButton->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));

            $form->addElement($loginButton);

            if ($a->allowUserSignUp()) {
                $signupButton = $form->createElement(
                    'button',
                    'signup_' . $adapter->adapterKey,
                    array('label' => 'ot-login-index:signUp')
                );
                $signupButton->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));
                $signupButton->setAttrib('class', 'signup');

                $form->addElement($signupButton);
            }

            $redirectUriHidden = $form->createElement('hidden', 'redirectUri');
            $redirectUriHidden->setValue($_SERVER['REQUEST_URI']);
            $redirectUriHidden->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));
            $form->addElement($redirectUriHidden);

            $realmHidden = $form->createElement('hidden', 'realm');
            $realmHidden->setValue($adapter->adapterKey);
            $realmHidden->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));

            $form->addElement($realmHidden);

            if(!$adapter->adapterKey) {
                throw new Ot_Exception_Data('ot-login-index:adapterMissingKey');
            }

            $loginForms[$adapter->adapterKey] = array(
                'form'        => $form,
                'realm'       => $adapter->adapterKey,
                'name'        => $adapter->name,
                'description' => $adapter->description,
                'autoLogin'   => $a->autoLogin(),
            );
        }
        if(count($loginForms) == 0) {
            throw new Ot_Exception('No login adapters found.');
        }

        $this->view->loginForms = $loginForms;

        $formUserId   = null;
        $formPassword = null;
        $validForm    = false;

        $get = Zend_Registry::get('getFilter');

        if (isset($get->realm) && $get->realm) {
            $realm = $get->realm;
        }

        if ($this->_request->isPost()) {
            $form = $loginForms[$realm]['form'];

            if (!$form->isValid($_POST)) {
                $realm = $form->getValue('realm');
                if (isset($loginForms[$realm]) && $loginForms[$realm]['autoLogin']) {
                    $formUserId = '';
                    $formPassword = '';
                    $validForm = true;
                }
                $this->_helper->messenger->addError('msg-error-invalidFormInfo');
            } else {
                $validForm = true;
            }
        }

        if ((isset($authRealm->realm) && $authRealm->autoLogin) || ($this->_request->isPost() && $validForm)) {

            if (isset($authRealm->realm) && !$this->_request->isPost()) {
                $realm = $authRealm->realm;
            } else {
                if($form->getValue('realm')) {
                    $realm = $form->getValue('realm');
                }
            }

            $username = ($formUserId) ? $formUserId : $form->getValue('username');
            $password = ($formPassword) ? $formPassword : $form->getValue('password');
            $redirectUri = ($form->getValue('redirectUri'));

            $authAdapter = new Ot_Model_DbTable_AuthAdapter();
            $adapter     = $authAdapter->find($realm);
            $className   = (string)$adapter->class;

            // Set up the authentication adapter
            $authAdapter = new $className($username, $password, $redirectUri);

            $auth = Zend_Auth::getInstance();
            $authRealm->realm = $realm;
            $authRealm->autoLogin = $authAdapter->autoLogin();

            // Attempt authentication, saving the result
            $result = $auth->authenticate($authAdapter);

            $authRealm->unsetAll();

            if ($result->isValid()) {
                $username = $auth->getIdentity()->username;

                $realm    = $auth->getIdentity()->realm;
                $account     = new Ot_Model_DbTable_Account();
                $thisAccount = $account->getByUsername($username, $realm);

                if (is_null($thisAccount)) {

                    $password = $account->generatePassword();

                    $acctData = array(
                        'username'  => $username,
                        'password'  => md5($password),
                        'realm'     => $realm,
                        'role'      => $registry->newAccountRole->getValue(),
                        'lastLogin' => time(),
                    );

                    $identity = $auth->getIdentity();

                    if (isset($identity->firstName)) {
                        $acctData['firstName'] = $identity->firstName;
                    }

                    if (isset($identity->lastName)) {
                        $acctData['lastName'] = $identity->lastName;
                    }

                    if (isset($identity->emailAddress)) {
                        $acctData['emailAddress'] = $identity->emailAddress;
                    }

                    if ($loginOptions['generateAccountOnLogin'] != 1) {
                        $auth->clearIdentity();
                        $authAdapter->autoLogout();
                        throw new Ot_Exception_Access('msg-error-createAccountNotAllowed');
                    }

                    $accountId = $account->insert($acctData);

                    $role = $acctData['role'];

                    $thisAccount = new stdClass();
                    $thisAccount->accountId = $accountId;
                    $thisAccount->username  = $acctData['username'];
                    $thisAccount->realm     = $realm;
                    $thisAccount->role      = $role;
                } else {
                    $role = $thisAccount->role;

                    $data = array('accountId' => $thisAccount->accountId, 'lastLogin' => time());
                    $account->update($data, null);
                }

                $auth->getStorage()->write($thisAccount);

                $loggerOptions = array(
                    'accountId'     => $thisAccount->accountId,
                    'role'          => (is_array($thisAccount->role)) ? implode(',', $thisAccount->role) : $thisAccount->role,
                    'attributeName' => 'accountId',
                    'attributeId'   => $thisAccount->accountId,
                );

                $this->_helper->log(Zend_Log::INFO, 'User ' . $username . ' logged in.', $loggerOptions);

                if (isset($req->uri) && $req->uri != '') {
                    $uri = $req->uri;
                    if(!strstr($uri, 'oauth_verif')) {
//var_dump('redir to ', $req->requestUri, $req->uri, $req->requestedFromUrl);exit;
                    }
                    $req->unsetAll();



                    return $this->_helper->redirector->gotoUrl($uri);
                } else {
                    return $this->_helper->redirector->gotoRoute(array(), 'default', true);
                }
            } else {
                if (count($result->getMessages()) == 0) {
                    $this->_helper->messenger->addError('msg-error-invalidUsername');
                } else {
                    $messages = array_merge($messages, $result->getMessages());
                }
            }
        }

        // If we have a single adapter that auto logs in, we forward on.
        if (count($loginForms) == 1) {

            $method = array_pop($loginForms);

            if ($method['autoLogin']) {
                $authRealm->realm = $method['realm'];
                $authRealm->autoLogin = true;

                return $this->_helper->redirector->gotoRoute(array('realm' => $authRealm->realm), 'login', true);
            }
        }

        if (isset($req->uri) && $req->uri != '') {
            $messages[] = 'msg-info-loginBeforeContinuing';
        }

        $this->view->realm = $realm;
        $this->view->messages = array_merge($this->_helper->messenger->getMessages(), $messages);

    }

    /**
     * Action for forgetting a password
     *
     */
    public function forgotAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
            return;
        }

        $realm = $this->_getParam('realm', null);

        if (is_null($realm)) {
            throw new Ot_Exception_Input('msg-error-realmNotFound');
        }

        // Set up the auth adapter
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter     = $authAdapter->find($realm);

        if (is_null($adapter)) {

            throw new Ot_Exception_Data(
                $this->view->translate('ot-login-signup:realmNotFound', array('<b>' . $realm . '</b>'))
            );
        }

        if ($adapter->enabled == 0) {
            throw new Ot_Exception_Access('msg-error-authNotSupported');
        }

        $className = (string)$adapter->class;
        $auth = new $className();

        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('msg-error-authNotSupported');
        }

        $form = new Ot_Form_ForgotPassword();

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $account = new Ot_Model_DbTable_Account();

                $userAccount = $account->getByUsername($form->getValue('username'), $realm);

                if (!is_null($userAccount)) {
                    $loginOptions = array();
                    $loginOptions = Zend_Registry::get('applicationLoginOptions');

                    // Generate key
                    $text   = $userAccount->username . '@' . $userAccount->realm . '-' . time();
                    $key    = $loginOptions['forgotpassword']['key'];
                    $iv     = $loginOptions['forgotpassword']['iv'];
                    $cipher = $loginOptions['forgotpassword']['cipher'];

                    $code = bin2hex(mcrypt_encrypt($cipher, $key, $text, MCRYPT_MODE_CBC, $iv));

                    $this->_helper->messenger->addSuccess('msg-info-passwordResetRequest');

                    $loggerOptions = array('attributeName' => 'accountId', 'attributeId' => $userAccount->accountId);

                    $this->_helper->log(Zend_Log::INFO, 'User ' . $userAccount->username . ' sent a password reset request.', $loggerOptions);

                    $dt = new Ot_Trigger_Dispatcher();
                    $dt->setVariables(array(
                        'firstName'    => $userAccount->firstName,
                        'lastName'     => $userAccount->lastName,
                        'emailAddress' => $userAccount->emailAddress,
                        'username'     => $userAccount->username,
                        'resetUrl'     => Zend_Registry::get('siteUrl') . '/login/password-reset/?key=' . $code,
                        'loginMethod'  => $userAccount->realm,
                    ));

                    $dt->dispatch('Login_Index_Forgot');

                    $this->_helper->redirector->gotoRoute(array('realm' => $realm), 'login', true);
                } else {
                    $this->_helper->messenger->addError('msg-error-userAccountNotFound');
                }
            } else {
                $this->_helper->messenger->addError('msg-error-invalidFormInfo');
            }
        }

        $this->_helper->pageTitle('ot-login-forgot:title');
        $this->view->assign(array(
            'form' => $form,
        ));
    }

    /**
     * Action for forgetting a password
     *
     */
    public function passwordResetAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
            return;
        }

        $userKey = $this->_getParam('key', null);

        if (is_null($userKey)) {
            throw new Ot_Exception_Input('msg-error-noKeyFound');
        }

        $loginOptions = Zend_Registry::get('applicationLoginOptions');

        $key    = $loginOptions['forgotpassword']['key'];
        $iv     = $loginOptions['forgotpassword']['iv'];
        $cipher = $loginOptions['forgotpassword']['cipher'];
        $string = pack("H*", $userKey);

        $decryptKey = trim(mcrypt_decrypt($cipher, $key, $string, MCRYPT_MODE_CBC, $iv));

        if (!preg_match('/[^@]*@[^-]*-[0-9]*/', $decryptKey)) {
            throw new Ot_Exception_Input('msg-error-invalidKey');
        }

        $userId = preg_replace('/\-.*/', '', $decryptKey);
        $ts = preg_replace('/^[^-]*-/', '', $decryptKey);

        $timestamp = new Zend_Date($ts);

        $now = new Zend_Date();

        $now->subMinute((int)$loginOptions['forgotpassword']['numberMinutesKeyIsActive']);

        if ($timestamp->getTimestamp() < $now->getTimestamp()) {
            throw new Ot_Exception_Input('msg-error-keyExpired');
        }

        $realm = preg_replace('/^[^@]*@/', '', $userId);
        $username = preg_replace('/@.*/', '', $userId);

        // Set up the auth adapter
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter     = $authAdapter->find($realm);

        if (is_null($adapter)) {
            throw new Ot_Exception_Data(
                $this->view->translate('ot-login-signup:realmNotFound', array('<b>' . $realm . '</b>'))
            );
        }

        if ($adapter->enabled == 0) {
            throw new Ot_Exception_Access('msg-error-authNotSupported');
        }

        $className   = (string)$adapter->class;
        $auth        = new $className();

        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('msg-error-authNotSupported');
        }

        $account     = new Ot_Model_DbTable_Account();
        $thisAccount = $account->getByUsername($username, $realm);

        if (is_null($thisAccount)) {
            throw new Ot_Exception_Data('msg-error-userAccountNotFound');
        }

        $form = new Ot_Form_PasswordReset();

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                if ($form->getValue('password') == $form->getValue('passwordConf')) {

                    $data = array(
                        'accountId' => $thisAccount->accountId,
                        'password'  => md5($form->getValue('password')),
                    );

                    $account->update($data, null);

                    $this->_helper->messenger->addSuccess('msg-info-passwordReset');

                    $loggerOptions = array(
                        'attributeName' => 'accountId',
                        'attributeId' => $data['accountId'],
                    );

                    $this->_helper->log(Zend_Log::INFO, 'User reset their password', $loggerOptions);

                    $this->_helper->redirector->gotoRoute(array('realm' => $realm), 'login', true);
                } else {
                    $this->_helper->messenger->addError('msg-error-passwordsNotMatch');
                }
            } else {
                $this->_helper->messenger->addError('msg-error-invalidFormInfo');
            }
        }

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/scripts/ot/jquery.plugin.passStrength.js');

        $this->_helper->pageTitle('ot-login-passwordReset:title');

        $this->view->assign(array(
            'form' => $form,
        ));

    }

    /**
     * Logs a user out
     *
     */
    public function logoutAction()
    {
        $userId = Zend_Auth::getInstance()->getIdentity();

        // Set up the auth adapter
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();

        $adapter     = $authAdapter->find($userId->realm);

        $className   = (string)$adapter->class;
        $auth        = new $className();
        $auth->autoLogout();

        Zend_Auth::getInstance()->clearIdentity();

        $this->_helper->redirector->gotoRoute(array(), 'default', true);

    }

    /**
     * allows a user to signup for an account
     *
     */
    public function signupAction()
    {
        $realm = $this->_getParam('realm', null);

        if (is_null($realm)) {
            throw new Ot_Exception_Input('msg-error-realmNotFound');
        }

        // Set up the auth adapter
        $authAdapter = new Ot_Model_DbTable_AuthAdapter();
        $adapter     = $authAdapter->find($realm);

        if (is_null($adapter)) {

            throw new Ot_Exception_Data(
                $this->view->translate('ot-login-signup:realmNotFound', array('<b>' . $realm . '</b>'))
            );
        }

        if ($adapter->enabled == 0) {
            throw new Ot_Exception_Access('msg-error-authNotSupported');
        }

        $className   = (string)$adapter->class;
        $auth        = new $className();

        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('msg-error-authNotSupported');
        }

        if (!$auth->allowUserSignUp()) {
            throw new Ot_Exception_Access('msg-error-authNotAllowed');
        }

        $form = new Ot_Form_Signup();
        $form->removeElement('realm');

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                if ($form->getValue('password') == $form->getValue('passwordConf')) {

                    $accountData = array(
                        'username'     => $form->getValue('username'),
                        'password'     => md5($form->getValue('password')),
                        'realm'        => $realm,
                        'role'         => $this->_helper->configVar('newAccountRole'),
                        'emailAddress' => $form->getValue('emailAddress'),
                        'firstName'    => $form->getValue('firstName'),
                        'lastName'     => $form->getValue('lastName'),
                    );

                    $account = new Ot_Model_DbTable_Account();
                    if ($account->accountExists($accountData['username'], $accountData['realm'])) {
                        $this->_helper->messenger->addError('msg-error-usernameTaken');
                    } else {

                        $dba = Zend_Db_Table::getDefaultAdapter();
                        $dba->beginTransaction();

                        try {
                            $accountData['accountId'] = $account->insert($accountData);
                        } catch (Exception $e) {
                            $dba->rollback();
                            throw $e;
                        }

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

                        $dba->commit();

                        $this->_helper->messenger->addSuccess('msg-info-accountCreated');

                        $loggerOptions = array(
                            'attributeName' => 'accountId',
                            'attributeId' => $accountData['accountId'],
                        );

                        $this->_helper->log(
                            Zend_Log::INFO, 'User ' . $accountData['username'] . ' created an account.', $loggerOptions
                        );

                        $dt = new Ot_Trigger_Dispatcher();
                        $dt->setVariables($accountData);
                        $dt->password    = $form->getValue('password');
                        $dt->loginMethod = $realm;
                        $dt->dispatch('Login_Index_Signup');

                        return $this->_helper->redirector->gotoRoute(array('realm' => $realm), 'login', true);
                    }
                } else {
                    $this->_helper->messenger->addError('msg-error-passwordsNotMatch');
                }
            } else {
                $this->_helper->messenger->addError('msg-error-invalidFormInfo');
            }
        }

        $this->_helper->pageTitle('ot-login-signup:title');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/scripts/ot/jquery.plugin.passStrength.js');

        $this->view->assign(array(
            'realm' => $realm,
            'form'  => $form,
        ));
    }
}