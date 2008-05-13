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
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to set a customized account linked to their user ID.
 *
 * @package    Account_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Account_IndexController extends Internal_Controller_Action 
{
   /**
     * Authz adapter
     *
     * @var Ot_Authz_Adapter
     */
    protected $_authzAdapter = null;
    
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
     * Flash Messenger Object
     *
     * @var unknown_type
     */
    protected $_flashMessenger = null;

    /**
     * Runs when the class is initialized.  For the accounts controller, some
     * users are allowed to access others accounts.  For them, we mask as 
     * that user to provide the required functionality
     *
     */
    public function init()
    {
        $this->_flashMessenger = $this->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('account');
        
        parent::init();
        
        $config = Zend_Registry::get('appConfig');

        $this->_authzAdapter = new $config->authorization(Zend_Auth::getInstance()->getIdentity());
        
        $filter = new Zend_Filter_Input(array('*' => 'StringTrim'), array(), $_GET);
        
        $userData = array();
        
        $userData['userId'] = Zend_Auth::getInstance()->getIdentity();
        if ($filter->userId && $this->_acl->isAllowed($this->_role, 'account_index', 'editAllAccounts')) {
            $userData['userId'] = $filter->userId;
        }
        
        $userData['displayUserId'] = preg_replace('/@.*$/', '', $userData['userId']);
        $userData['realm']         = preg_replace('/^[^@]*@/', '', $userData['userId']);     
                
        $a = $config->authentication->{$userData['realm']};
        $this->_authAdapter = new $a->class;
        $userData['authAdapter'] = array(
           'realm'       => $userData['realm'],
           'name'        => $a->name,
           'description' => $a->description,
        );
        
        try {
            $user = $this->_authzAdapter->getUser($userData['userId']);
        } catch (Exception $e) {
            if ((bool)$config->loginOptions->generateAuthzInstanceOnFirstLogin) {
                throw $e;
            }
            
            throw new Ot_Exception_Data('User account creation reqiured on login, but no authorization data was found.');
        }
        
        $userData['role'] = $user['role'];
        
        $account = new Ot_Account();        
        $thisAccount = $account->find($userData['userId']);
        
        if (!is_null($thisAccount)) {
            $userData = array_merge($userData, $thisAccount->toArray());
        }               
        
        $this->_userData = $userData;
        
    }   
    
    /**
     * Default user profile page 
     *
     */
    public function indexAction()
    {
        $config = Zend_Registry::get('appConfig');

        $this->view->acl = array(
            'edit'            => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete'          => ($this->_acl->isAllowed($this->_role, $this->_resource, 'delete') && $this->_userData['userId'] != Zend_Auth::getInstance()->getIdentity()),
            'generateApiCode' => $this->_acl->isAllowed($this->_role, $this->_resource, 'generate-api-code'),
            'deleteApiCode'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete-api-code'),
            'changePassword'  => $this->_authAdapter->manageLocally() && 
                $this->_userData['userId'] == Zend_Auth::getInstance()->getIdentity() &&
                $this->_acl->isAllowed($this->_role, $this->_resource, 'change-password'),
        );

        $remote = (boolean)$config->remoteAccess->allow;
        
        if ($remote) {
            $apiCode = new Ot_Api_Code();
            
            $code = $apiCode->find($this->_userData['userId']);
        
            if (!is_null($code)) {
                $this->view->apiCode = $code->code;
            }
        }
        
        $this->view->messages = $this->_flashMessenger->getMessages();
        $this->view->remote   = $remote;
        $this->view->userData = $this->_userData;
        $this->view->title    = "Account for " . ((isset($this->_userData['firstName'])) ? $this->_userData['firstName'] . ' ' . $this->_userData['lastName'] : $this->_userData['userId']);
    }
    
    /**
     * Display a list of all users in the system.
     *
     */
    public function allAction()
    {
        $users = $this->_authzAdapter->getUsers();

        $this->view->acl = array(
            'add'    => $this->_acl->isAllowed($this->_role, $this->_resource, 'add'),
            'edit'   => $this->_acl->isAllowed($this->_role, $this->_resource, 'edit'),
            'delete' => $this->_acl->isAllowed($this->_role, $this->_resource, 'delete'),
        );

        if (count($users) != 0) {
            $this->view->javascript = 'sortable.js';
        }

        $config = Zend_Registry::get('appConfig');
        $this->view->realms = $config->authentication->toArray();
        
        $this->view->title = "Manage Users";
        $this->view->users = $users;
        $this->view->messages = $this->_flashMessenger->getMessages();      
    }
    
    /**
     * Adds a user to the system
     *
     */
    public function addAction()
    {
        $config = Zend_Registry::get('appConfig');

        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'addUser')
             ;
        
        $adapters = $config->authentication->toArray();
        
        $realmSelect = new Zend_Form_Element_Select('realm');
        $realmSelect->setLabel('Login Method:');
        
        foreach ($adapters as $key => $value) {
            $a = new $value['class'];
            
            $class = array();
            
            if ($a->autoLogin()) {
                $class[] = 'autoLogin';
            } else {
                $class[] = 'manualLogin';
            }
            
            if ($a->allowUserSignUp()) {
                $class[] = 'signup';
            } else {
                $class[] = 'noSignup';
            }
            
            $realmSelect->addMultiOption($key, $value['name']);
            
            $hidden = new Zend_Form_Element_Hidden($key);
            $hidden->setAttrib('class', implode(' ', $class));
            $hidden->setValue($value['description']);
            $hidden->clearDecorators();
            $hidden->addDecorators(array(
                array('ViewHelper'),    // element's view helper
            ));
            
            $form->addElement($hidden, $key);
        }
        
        // Create and configure username element:
        // Create and configure username element:
        $username = $form->createElement('text', 'username', array('label' => 'Username:'));
        $username->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('Alnum')
                 ->addFilter('StripTags')
                 ->setAttrib('maxlength', '64')
                 ;
                 
        $password = $form->createElement('password', 'password', array('label' => 'Password:'));
        $password->setRequired(true)
                 ->addValidator('StringLength', false, array(6, 20))
                 ->addFilter('StringTrim')
                 ->addFilter('StripTags')
                 ;   

        $passwordConf = $form->createElement('password', 'passwordConf', array('label' => 'Confirm Password:'));
        $passwordConf->setRequired(true)
                     ->addValidator('StringLength', false, array(6, 20))
                     ->addFilter('StringTrim')
                     ->addFilter('StripTags')
                     ;    

        $firstName = $form->createElement('text', 'firstName', array('label' => 'First Name:'));
        $firstName->setRequired(true)
                  ->addFilter('StringToLower')
                  ->addFilter('StringTrim')
                  ->addFilter('StripTags')
                  ->setAttrib('maxlength', '64')
                  ;

        $lastName = $form->createElement('text', 'lastName', array('label' => 'Last Name:'));
        $lastName->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('StringToLower')
                 ->addFilter('StripTags')
                 ->setAttrib('maxlength', '64')
                 ;
        
        $email = $form->createElement('text', 'emailAddress', array('label' => 'Email Address:'));
        $email->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress')
              ;
        
        if ($this->_authzAdapter->manageLocally()) {
            $roleSelect = new Zend_Form_Element_Select('role');
            $roleSelect->setLabel('Access Role:');
    
            $roles = $this->_acl->getAvailableRoles();     
               
            foreach ($roles as $r) {
                $roleSelect->addMultiOption($r['name'], $r['name']);
            }
            
            $form->addElement($roleSelect);
        }

        $form->addElements(array($username, $realmSelect, $firstName, $lastName, $email))
             ->addDisplayGroup(array('username', 'realm', 'role', 'firstName', 'lastName', 'emailAddress'), 'fields')
             ->addElement('submit', 'addButton', array('label' => 'Add Account'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;        

        $messages = array();
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
    
                $username = $form->getValue('username');
                $role     = $form->getValue('role');
                $realm    = $form->getValue('realm');
                
                $userId = $username . '@' . $realm;  

                $accountData = array(
                   'userId'       => $userId,
                   'firstName'    => ucwords($form->getValue('firstName')),
                   'lastName'     => ucwords($form->getValue('lastName')),
                   'emailAddress' => $form->getValue('emailAddress'),
                );
                
                $authAdapter  = new $config->authentication->$realm->class();
                $authzAdapter = new $config->authorization($userId);
                
                $dba = Zend_Registry::get('dbAdapter');
                
                $dba->beginTransaction();
                
                if ($authAdapter->manageLocally()) {

                    $user = $authAdapter->getUser($userId);
                
                    if (count($user) == 0) { 
                        
                        try {           
                           $password = $authAdapter->addAccount($userId, '');
                        } catch (Exception $e) {
                            $dba->rollback();
                            throw $e;      
                        }
                    } else {
                        $messages[] = 'Username is taken.  Please select a different username';                
                    }
                }        
                                
                if (count($messages) == 0 && $authzAdapter->manageLocally()) {
                       
                    try {
                        $user = $authzAdapter->getUser($userId);
                        
                        $messages[] = 'Username is taken.  Please select a different username';
                    } catch (Exception $e) {
                        try {
                            $authzAdapter->addUser($userId, $role);
                        } catch (Exception $e) {
                            $dba->rollback();
                            throw $e;
                        }
                    }
                }

                if (count($messages) == 0) {
                    $account = new Ot_Account();
                    
                    $thisAccount = $account->find($userId);
                    
                    if (is_null($thisAccount)) {
                        try {
                            $account->insert($accountData);
                        } catch (Exception $e) {
                            $dba->rollback();
                            throw $e;
                        }
                    } else {
                        $messages[] = 'Username is taken.  Please select a different username';
                    }
                    
                    $dba->commit();
                    
                    $this->_flashMessenger->addMessage('The account has been created.');
                    
                    $trigger = new Ot_Trigger();
                    $trigger->setVariables($accountData);
                    $trigger->username    = $username;
                    $trigger->loginMethod = $config->authentication->$realm->name;
                    $trigger->role        = $role;
                    
                    if ($authAdapter->manageLocally()) {
                        $trigger->password = $password;
                        
                        $this->_flashMessenger->addMessage('A password has been created to the account and emailed to the user.');
                        
                        $trigger->dispatch('Admin_Account_Create_Password');
                    } else {
                        $trigger->dispatch('Admin_Account_Create_NoPassword');
                    }
                    
                    $this->_logger->setEventItem('attributeName', 'userId');
                    $this->_logger->setEventItem('attributeId', $userId);
                    $this->_logger->info('Account was added for ' . $userId . '.');
        
                    $this->_helper->redirector->gotoUrl('/account/index/all/'); 
                }
            } else {
                $messages[] = 'You did not fill in valid information into the form.';
            }
        }
        
        $this->view->messages = $messages;
        $this->view->title = 'Add Account';
        $this->view->form = $form;

    }   
    
    /**
     * Edits an existing user
     *
     */
    public function editAction()
    {
        $form = new Zend_Form();
        $form->setAction('?userId=' . $this->_userData['userId'])
             ->setMethod('post')
             ->setAttrib('id', 'editUser')
             ;
        
        $hidden = new Zend_Form_Element_Hidden('realm');
        $hidden->setValue($this->_userData['realm']);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
            
        $form->addElement($hidden, 'realm');
        
        $realmStatic = $form->createElement('text', 'realmStatic', array('label' => 'Login Method:'));
        $realmStatic->setValue($this->_userData['authAdapter']['name'])
                    ->setAttrib('readonly', true)
                    ;
        
        $hidden = new Zend_Form_Element_Hidden('username');
        $hidden->setValue($this->_userData['displayUserId']);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
            
        $form->addElement($hidden, 'username');
                         
        // Create and configure username element:
        $usernameStatic = $form->createElement('text', 'usernameStatic', array('label' => 'Username:'));
        $usernameStatic->setValue($this->_userData['displayUserId'])
                       ->setAttrib('readonly', true)
                       ->addFilter('StringTrim');
        
        if ($this->_authzAdapter->manageLocally()) {
            if ($this->_acl->isAllowed($this->_role, 'account_index', 'changeUserRole')) {
                $roleSelect = new Zend_Form_Element_Select('role');
                $roleSelect->setLabel('Access Role:');
        
                $roles = $this->_acl->getAvailableRoles();     
                   
                foreach ($roles as $r) {
                    $roleSelect->addMultiOption($r['name'], $r['name']);
                }
                
                $roleSelect->setValue($this->_userData['role']);
                
                $form->addElement($roleSelect);
            } else {
                $roleStatic = $form->createElement('text', 'role', array('label' => 'Access Role:'));
                $roleStatic->setRequired(true)
                               ->setValue($this->_userData['role'])
                               ->setAttrib('readonly', true)
                               ->addFilter('StringTrim');        
    
                $form->addElement($roleStatic);                           
            }
        }
              
        $firstName = $form->createElement('text', 'firstName', array('label' => 'First Name:'));
        $firstName->setRequired(true)
                  ->addFilter('StringToLower')
                  ->addFilter('StringTrim')
                  ->addFilter('StripTags')
                  ->setAttrib('maxlength', '64')
                  ->setValue((isset($this->_userData['firstName'])) ? $this->_userData['firstName'] : '')
                  ;

        $lastName = $form->createElement('text', 'lastName', array('label' => 'Last Name:'));
        $lastName->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('StringToLower')
                 ->addFilter('StripTags')
                 ->setAttrib('maxlength', '64')
                 ->setValue((isset($this->_userData['lastName'])) ? $this->_userData['lastName'] : '')
                 ;
        
        $email = $form->createElement('text', 'emailAddress', array('label' => 'Email Address:'));
        $email->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress')
              ->setValue((isset($this->_userData['emailAddress'])) ? $this->_userData['emailAddress'] : '')
              ;              

        $form->addElement($realmStatic, 'realmStatic')
             ->addElement($usernameStatic)
             ->addElements(array($firstName, $lastName, $email))
             ;
             
        $form->addDisplayGroup(array('usernameStatic', 'realmStatic', 'role', 'firstName', 'lastName', 'emailAddress'), 'fields')
             ->addElement('submit', 'editButton', array('label' => 'Save User'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;           


        $messages = array();
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $dba = Zend_Registry::get('dbAdapter');
                
                $dba->beginTransaction();
                
                if ($this->_authzAdapter->manageLocally()) {
                    try {
                       $this->_authzAdapter->editUser($this->_userData['userId'], $form->getValue('role'));
                    } catch (Exception $e) {
                        $dba->rollback();
                        throw $e;
                    }
                }

                $data = array(
                    'userId'       => $this->_userData['userId'],
                    'firstName'    => ucwords($form->getValue('firstName')),
                    'lastName'     => ucwords($form->getValue('lastName')),
                    'emailAddress' => $form->getValue('emailAddress'),
                );
                
                $account = new Ot_Account();
                
                try {
                    if ($this->_userData['firstName'] != '') {
                        $account->update($data, null);
                    } else {
                        $account->insert($data);
                    }
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
                
                $dba->commit();
                
                $this->_logger->setEventItem('attributeName', 'userId');
                $this->_logger->setEventItem('attributeId', $this->_userData['userId']);
                $this->_logger->info('Account was modified for ' . $this->_userData['userId'] . '.');
    
                $this->_flashMessenger->addMessage('The account was successfully updated');
                $this->_helper->redirector->gotoUrl('/account/?userId=' . $this->_userData['userId']);
            } else {
                $messages[] = 'The form you submitted was not vaild';
            }
        }

        $this->view->messages = $messages;
        $this->view->form = $form;
        $this->view->title  = 'Edit User';
    }

    /**
     * Deletes a user
     *
     */
    public function deleteAction()
    {   
        if ($this->_userData['userId'] == Zend_Auth::getInstance()->getIdentity()) {
            throw new Ot_Exception_Access('You are not allowed to delete yourself.');
        }
        
        $form = new Zend_Form();
        $form->setAction('?userId=' . $this->_userData['userId'])
             ->setMethod('post')
             ->setAttrib('id', 'deleteUser')
             ;
        
        $form->addElement('submit', 'deleteButton', array('label' => 'Delete User'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;                   
       

        if ($this->_request->isPost() && $form->isValid($_POST)) {
            
            $dba = Zend_Registry::get('dbAdapter');
            $dba->beginTransaction();
            
            if ($this->_authAdapter->manageLocally()) {
                try {
                    $this->_authAdapter->deleteAccount($this->_userData['userId']);
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
            }
            
            if ($this->_authzAdapter->manageLocally()) {
                try {
                    $this->_authzAdapter->deleteUser($this->_userData['userId']);
                } catch (Exception $e) {
                    $dba->rollback();
                    throw $e;
                }
            }
            
            $account = new Ot_Account();
            
            $where = $account->getAdapter()->quoteInto('userId = ?', $this->_userData['userId']);
            
            try {
                $account->delete($where);
            } catch (Exception $e) {
                $dba->rollback();
                throw $e;
            }

            $dba->commit();
            $this->_logger->setEventItem('attributeName', 'userId');
            $this->_logger->setEventItem('attributeId', $this->_userData['userId']);
            $this->_logger->info('Account was deleted for ' . $this->_userData['userId'] . '.');

            $this->_flashMessenger->addMessage('The account was successfully updated');
            
            $this->_helper->redirector->gotoUrl('/account/index/all/');

        }
        
        $this->view->userData = $this->_userData;
        $this->view->title  = 'Delete User';
        $this->view->form = $form;
    }
    
    /**
     * allows a user to change their password
     *
     */
    public function changePasswordAction()
    {
        $userId = Zend_Auth::getInstance()->getIdentity();
            
        $realm = preg_replace('/^[^@]*@/', '', $userId);
            
        $config   = Zend_Registry::get('appConfig');
        $auth = new $config->authentication->$realm->class();
        
        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('The authentication adapter for your account does not support this feature');
        }
        
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'changePassword')
             ;
                         
        $oldPassword = $form->createElement('password', 'oldPassword', array('label' => 'Old Password:'));
        $oldPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(6, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ;   
                    
        $newPassword = $form->createElement('password', 'newPassword', array('label' => 'New Password:'));
        $newPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(6, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ; 
                     
        $newPasswordConf = $form->createElement('password', 'newPasswordConf', array('label' => 'New Password Confirm:'));
        $newPasswordConf->setRequired(true)
                        ->addValidator('StringLength', false, array(6, 20))
                        ->addFilter('StringTrim')
                        ->addFilter('StripTags')
                        ;    

        $form->addElements(array($oldPassword, $newPassword, $newPasswordConf))
             ->addDisplayGroup(array('oldPassword', 'newPassword', 'newPasswordConf'), 'fields')
             ->addElement('submit', 'changeButton', array('label' => 'Change My Password'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;         

        $messages = array();
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                $user = $auth->getUser($userId);
                if (count($user) != 1) {
                    throw new Ot_Exception_Data('User account not found');
                }
            
                $user = $user[0];
    
                if ($form->getValue('newPassword') != $form->getValue('newPasswordConf')) {
                    $messages[] = 'New passwords do not match';
                }
    
                if ($auth->encryptPassword($form->getValue('oldPassword')) != $user['password']) {
                    $messages[] = 'Original Password was incorrect';
                }
    
                if (count($messages) == 0) {
                    $auth->editAccount($userId, $form->getValue('newPassword'));
                
                    $this->_flashMessenger->addMessage('Your password has been changed.  You can now log in with your new credentials');
                    
                    $this->_logger->setEventItem('attributeName', 'userId');
                    $this->_logger->setEventItem('attributeId', $userId);
                    $this->_logger->info('User changed Password'); 
                    
                    $this->_helper->redirector->gotoUrl('/account/');
                }
            } else {
                $messages[] = 'There were errors with part of the form.';
            }
        } 
        
        $this->view->javascript = array('mooStrength.js');
        $this->view->messages = $messages;
        $this->view->title = 'Change your password';
        $this->view->form  = $form;
    }      
    
    /**
     * Add an API code so the user can access the application through the SOAP API
     *
     */
    public function generateApiCodeAction()
    {
        $config = Zend_Registry::get('appConfig');

        if (!(boolean)$config->remoteAccess->allow) {
            throw new Ot_Exception_Access('This application is not configured for remote access');      
        }
        
        $apiCode = new Ot_Api_Code();

        $apiCode->generateCodeForUser($this->_userData['userId']);
        
        $this->_logger->setEventItem('attributeName', 'apiCode');
        $this->_logger->setEventItem('attributeId', $this->_userData['userId']);
        $this->_logger->info('API Code generated');
                
        $this->_flashMessenger->addMessage('The API Code was successfully created.  This user has remote access now.');
        
        $this->_helper->redirector->gotoUrl('/account/?userId=' . $this->_userData['userId']);       
    }

    /**
     * Deletes an API code to remove the users remote access
     *
     */
    public function deleteApiCodeAction()
    {
        $form = new Zend_Form();
        $form->setAction('?userId=' . $this->_userData['userId'])
             ->setMethod('post')
             ->setAttrib('id', 'deleteApiCode')
             ;
        
        $form->addElement('submit', 'deleteButton', array('label' => 'Delete Api Code'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;                   
       

        if ($this->_request->isPost() && $form->isValid($_POST)) {

            $apiCode = new Ot_Api_Code();
            
            $where = $apiCode->getAdapter()->quoteInto('userId = ?', $this->_userData['userId']);
            
            $apiCode->delete($where);

            $this->_logger->setEventItem('attributeName', 'userId');
            $this->_logger->setEventItem('attributeId', $this->_userData['userId']);
            $this->_logger->info('Account was deleted for ' . $this->_userData['userId'] . '.');

            $this->_flashMessenger->addMessage('The API Code was successfully deleted.  This user has no more remote access.');
            
            $this->_helper->redirector->gotoUrl('/account/?userId=' . $this->_userData['userId']);

        }
        
        $this->view->userData = $this->_userData;
        $this->view->title  = 'Delete API Code';
        $this->view->form = $form;
    }

    /**
     * Used to determine if a user should be allowed to change the roles of other users
     *
     */
    public function changeUserRoleAction()
    {}
    
    /**
     * Used to determine if a user should be allowed to edit all accounts
     *
     */
    public function editAllAccountsAction()
    {}
}