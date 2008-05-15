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
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to log in and log out of the application, as well as signup
 * for new accounts and reset passwords.
 *
 * @package    Login_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Login_IndexController extends Internal_Controller_Action 
{
	/**
	 * Flash Manager to handle display notifications
	 *
	 * @var object
	 */
	protected $_flashMessenger = null;
	
	/**
	 * Initialization function
	 *
	 */
	public function init()
	{
		$this->_flashMessenger = $this->getHelper('FlashMessenger');
		$this->_flashMessenger->setNamespace('login');
		
		parent::init();
	}

    /**
     * Action when going to the main login page
     *
     */
    public function indexAction()
    {    	
        $this->view->title = "Login";

        $config = Zend_Registry::get('appConfig');

        $authRealm = new Zend_Session_Namespace('authRealm');
        $authRealm->setExpirationHops(1);

        if (Zend_Auth::getInstance()->hasIdentity()) {
        	$this->_redirect('/');
        }
        
        $filter = new Zend_Filter_Input(array('*' => 'StringTrim'), array(), $_GET);
        
        $appConfig = Zend_Registry::get('appConfig');
        
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'login')
             ;
        
        $adapters = $appConfig->authentication->toArray();
              
        $sel = new Zend_Form_Element_Select('realm');
        $sel->setLabel('Login Method:');
        
        $form->addElement($sel, 'realm');
        
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
            
            $sel->addMultiOption($key, $value['name']);
            
            $hidden = new Zend_Form_Element_Hidden($key);
            $hidden->setAttrib('class', implode(' ', $class));
            $hidden->setValue($value['description']);
            $hidden->clearDecorators();
            $hidden->addDecorators(array(
                array('ViewHelper'),    // element's view helper
            ));
            
            $form->addElement($hidden, $key);
        }
                  
        $sel->setValue($filter->realm);
        
        // Create and configure username element:
        $username = $form->createElement('text', 'username', array('label' => 'Username:'));
        $username->setRequired(true)
                 ->addFilter('StringTrim');
        
        // Create and configure password element:
        $password = $form->createElement('password', 'password', array('label' => 'Password:'));
        $password->addFilter('StringTrim')
                 ->setRequired(true);

        $form->addElement($username)
             ->addElement($password)
             ->addDisplayGroup(array('realm', 'username', 'password'), 'fields')
             ->addElement('submit', 'loginButton', array('label' => 'Login'))
             ->addElement('button', 'signup', array('label' => 'Sign-Up Now'))
             ;
        
        $formUserId   = null;
        $formPassword = null;
        $validForm    = false;
        $messages     = array();
        
        if ($this->_request->isPost()) {
        	
        	if (!$form->isValid($_POST)) {
        		$realm = $form->getValue('realm');
        		
        		$realmElement = $form->getElement($realm);
        		if (!preg_match('/manualLogin/i', $realmElement->getAttrib('class'))) {
        			$formUserId = '';
        			$formPassword = '';
        			$validForm = true;
        		}
        		$messages[] = 'You did not fill in valid information into the form.';
        	} else {
        		$validForm = true;
        	}
        }
        
        if ((isset($authRealm->realm) && $authRealm->autoLogin) || ($this->_request->isPost() && $validForm)) {

            if (isset($authRealm->realm) && !$this->_request->isPost()) {
            	$realm = $authRealm->realm;
            } else {
                $realm = $form->getValue('realm');
            }
            
            $userId   = ($formUserId) ? $formUserId : $form->getValue('username') . '@' . $realm;
            $password = ($formPassword) ? $formPassword : $form->getValue('password');
            
            // Set up the authentication adapter
            $authAdapter = new $config->authentication->$realm->class($userId, $password);
            $auth = Zend_Auth::getInstance();            
            
            $authRealm->realm = $realm;
            $authRealm->autoLogin = $authAdapter->autoLogin();
            
            // Attempt authentication, saving the result
            $result = $auth->authenticate($authAdapter);

            $authRealm->unsetAll();

            $userId = ($auth->hasIdentity()) ? $auth->getIdentity(): 'nouser';
            
            if ($result->isValid()) {
		                    
	            $authz = new $config->authorization($userId);
	            
	            try {
	                $user = $authz->getUser($userId);
	                
	                $role = $user['role'];
	            } catch (Exception $e) {
	            	if ($config->loginOptions->generateAccountOnFirstLogin == 1) {
	            	    $authz->addUser($userId, (string)$config->loginOptions->defaultRoleOnAccountCreation);
	            		$user = $authz->getUser($userId);
	            		$role = $user['role'];
	            	} else {
	            		$role = (string)$config->loginOptions->defaultAuthenticatedRole;
	            	}
	            }
	            
	            $this->_logger->setEventItem('userId', $auth->getIdentity());
	            $this->_logger->setEventItem('role', '');
	            $this->_logger->setEventItem('attributeName', 'userId');
	            $this->_logger->setEventItem('attributeId', $userId);
	            $this->_logger->login('User Logged In');  

	            if (isset($config->loginOptions->startpage->$role)) {
	            	$this->_helper->redirector->gotoUrl($config->loginOptions->startpage->$role);
	            } else {
		            $req = new Zend_Session_Namespace('request');
		            	
		            if (isset($req->uri) && $req->uri != '') {
		            	$this->_helper->redirector->gotoUrl($req->uri);
		            } else {
		               $this->_helper->redirector->gotoUrl('/');
		            }
	            }
            } else {
                $this->_logger->setEventItem('attributeName', 'userId');
                $this->_logger->setEventItem('attributeId', $userId);
                $this->_logger->info('Invalid Login Attempt'); 

                $messages[] = 'Your entered an invalid username/password.';
            }
        }

        $options = $form->getElement('realm')->getMultiOptions();
        
        // If we have a single adapter that auto logs in, we forward on.
        if (count($options) == 1) {
            $key = array_keys($options);
            
            $elm = $form->getElement($key[0]);
            
            if (preg_match('/autoLogin/i', $elm->class)) {
            	$authRealm->realm = $key[0];
                $authRealm->autoLogin = true;
            
                $this->_helper->redirector->gotoUrl('/login/');
            }
        }
        
        $this->view->messages = array_merge($this->_flashMessenger->getMessages(), $messages);
        $this->view->form = $form;
        
    }

    /**
     * Action for forgetting a password
     *
     */
    public function forgotAction()
    {
        $config = Zend_Registry::get('appConfig');
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector->gotoUrl('/');
            return;
        }            

        $filter = new Zend_Filter_Input(array('*' => 'StringTrim'), array(), $_GET);
        
        if (!$filter->realm) {
            throw new Ot_Exception_Input('Realm not found');
        }
        
        $realm = $filter->realm;
        
        $auth = new $config->authentication->$realm->class();
        
        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('The authentication adapter for your account does not support this feature');
        }   
        
        
        $form = new Zend_Form();
        $form->setAction('?realm=' . $realm)
             ->setMethod('post')
             ->setAttrib('id', 'forgotPassword')
             ;
        
        $hidden = new Zend_Form_Element_Hidden('realm');
        $hidden->setValue($realm);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
            
        $form->addElement($hidden, 'realm');
        
        $realmStatic = $form->createElement('text', 'realmStatic', array('label' => 'Login Method:'));
        $realmStatic->setValue($config->authentication->$realm->name)
                    ->setAttrib('readonly', true)
                    ;
                
        // Create and configure username element:
        $username = $form->createElement('text', 'username', array('label' => 'Username:'));
        $username->setRequired(true)
                 ->addFilter('StringTrim')
                 ;
        

        $form->addElement($realmStatic)
             ->addElement($username)
             ->addDisplayGroup(array('realmStatic', 'username'), 'fields')
             ->addElement('submit', 'resetPasswordButton', array('label' => 'Reset My Password'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;        
        

        $messages = array();
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {        
	            $userId = $form->getValue('username') . '@' . $realm;
	            
	            $account = new Ot_Account();
	            
	            $userAccount = $account->find($userId);
	            
	            if (!is_null($userAccount)) {	                
		                 
	            	// Generate key
	            	$text   = $userId . '-' . time();
                    $key    = (string)$config->loginOptions->passwordReset->cryptKey;
                    $iv     = (string)$config->loginOptions->passwordReset->iv;
                    $cipher = constant((string)$config->loginOptions->passwordReset->cipher);

                    $code = bin2hex(mcrypt_encrypt($cipher, $key, $text, MCRYPT_MODE_CBC, $iv));
  
			        $this->_flashMessenger->addMessage('A password reset request was sent to the email address on file');
				            
			        $this->_logger->setEventItem('attributeName', 'userId');
			        $this->_logger->setEventItem('attributeId', $userId);
			        $this->_logger->info('User sent password reset request'); 
			        
			        $et = new Ot_Trigger();
			        $et->setVariables($userAccount->toArray());
			        
			        $et->resetUrl    = Zend_Registry::get('siteUrl') . '/login/password-reset/?key=' . $code;
                    $et->username    = preg_replace('/@.*/', '', $userId);
                    $et->loginMethod = $config->authentication->$realm->name;
                    $et->dispatch('Login_Index_Forgot');
		            
                    die(Zend_Registry::get('siteUrl') . '/login/index/password-reset/?key=' . $code);
                    
		            $this->_helper->redirector->gotoUrl('/login/?realm=' . $realm);
	            } else {
	            	$messages[] = 'The user account you entered was not found';
	            }
            } else {
            	$messages[] = 'You did not fill in valid information into the form.';
            }
        }

        $this->view->messages = $messages;
        $this->view->title = "Forgot My Password";             
        $this->view->form = $form;
    }
    
    /**
     * Action for forgetting a password
     *
     */
    public function passwordResetAction()
    {
        $config = Zend_Registry::get('appConfig');
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector->gotoUrl('/');
            return;
        }            

        $filter = new Zend_Filter_Input(array('*' => 'StringTrim'), array(), $_GET);
        
        if (!$filter->key) {
            throw new Ot_Exception_Input('No Key Found');
        }
        
        $key    = (string)$config->loginOptions->passwordReset->cryptKey;
        $iv     = (string)$config->loginOptions->passwordReset->iv;
        $cipher = constant((string)$config->loginOptions->passwordReset->cipher);        
        $string = pack("H*", $filter->key);
    
        $decryptKey = trim(mcrypt_decrypt($cipher, $key, $string, MCRYPT_MODE_CBC, $iv));
        
        if (!preg_match('/[^@]*@[^-]*-[0-9]*/', $decryptKey)) {
        	throw new Ot_Exception_Input('The key is not valid');
        }
        
        $userId = preg_replace('/\-.*/', '', $decryptKey);
        $ts = preg_replace('/^[^-]*-/', '', $decryptKey);
        //die($ts);
        $timestamp = new Zend_Date($ts);
        
        $now = new Zend_Date();
        
        $now->subMinute((int)$config->loginOptions->passwordReset->numberMinutesKeyIsActive);
        
        if ($timestamp->getTimestamp() < $now->getTimestamp()) {
        	throw new Ot_Exception_Input('This key has expired.  You need to generate another request to reset your password and try again.');
        }
        
        $realm = preg_replace('/^[^@]*@/', '', $userId);
        
        $auth = new $config->authentication->$realm->class();
        
        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('The authentication adapter for your account does not support this feature');
        }   
        
        $account = new Ot_Account();
                
        $userAccount = $account->find($userId);
                
        if (is_null($userAccount)) {
        	throw new Ot_Exception_Data('User Account not found');
        }
        
        $form = new Zend_Form();
        $form->setAction('?key=' . $filter->key)
             ->setMethod('post')
             ->setAttrib('id', 'resetPassword')
             ;
        
        $hidden = new Zend_Form_Element_Hidden('realm');
        $hidden->setValue($realm);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
        
        $form->addElement($hidden, 'realm');
        
        $hidden = new Zend_Form_Element_Hidden('userId');
        $hidden->setValue($userId);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
            
        $form->addElement($hidden, 'userId');
        
        $realmStatic = $form->createElement('text', 'realmStatic', array('label' => 'Login Method:'));
        $realmStatic->setValue($config->authentication->$realm->name)
                    ->setAttrib('readonly', true)
                    ;
                
        // Create and configure username element:
        $usernameStatic = $form->createElement('text', 'usernameStatic', array('label' => 'Username:'));
        $usernameStatic->setValue(preg_replace('/@.*/', '', $userId))        
                       ->setAttrib('readonly', true)
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

        $form->addElement($realmStatic)
             ->addElement($usernameStatic)
             ->addElement($password)
             ->addElement($passwordConf)
             ->addDisplayGroup(array('realmStatic', 'usernameStatic', 'password', 'passwordConf'), 'fields')
             ->addElement('submit', 'resetPasswordButton', array('label' => 'Reset My Password'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;        
        

        $messages = array();
        
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {        
                if ($form->getValue('password') == $form->getValue('passwordConf')) {
                	
                    $this->_flashMessenger->addMessage('Your password has been reset');
                            
                    $auth->editAccount($userId, $form->getValue('password'));
                    
                    $this->_logger->setEventItem('attributeName', 'userId');
                    $this->_logger->setEventItem('attributeId', $userId);
                    $this->_logger->info('User reset their password'); 
                    
                    $this->_helper->redirector->gotoUrl('/login/?realm=' . $realm);
                } else {
                    $messages[] = 'The passwords you entered did not match';
                }
            } else {
                $messages[] = 'You did not fill in valid information into the form.';
            }
        }

        $this->view->messages = $messages;
        $this->view->title = "Forgot My Password";             
        $this->view->form = $form;
        $this->view->javascript = array('mooStrength.js');
    }    
    /**
     * Logs a user out
     *
     */
    public function logoutAction()
    {
    	//$this->_helper->getExistingHelper('viewRenderer')->setNeverRender();
    	//$this->_helper->layout->disableLayout();
    	
        $config = Zend_Registry::get('appConfig');
        
        $userId = Zend_Auth::getInstance()->getIdentity();
        foreach ($config->authentication as $a) { 
            $auth = new $a->class;
            $auth->autoLogout();  
        }
                
        $this->_logger->setEventItem('attributeName', 'userId');
        $this->_logger->setEventItem('attributeId', $userId);
        $this->_logger->login('User Logged Out');

        Zend_Auth::getInstance()->clearIdentity();
        
        Ot_Authz::getInstance()->clearRole();
                
        $this->_helper->redirector->gotoUrl('/index/index/'); 
    } 
    
    /**
     * allows a user to signup for an account
     *
     */
    public function signupAction()
    {    	
    	$config = Zend_Registry::get('appConfig');
    	
        $filter = new Zend_Filter_Input(array('*' => 'StringTrim'), array(), $_GET);
        
        if (!$filter->realm) {
            throw new Ot_Exception_Input('Realm not found');
        }
        
        $realm = $filter->realm;
        
        $auth = new $config->authentication->$realm->class();
        
        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('The authentication adapter for your account does not support this feature');
        }
        
        if (!$auth->allowUserSignUp()) {
            throw new Ot_Exception_Access('The authentication adapter for your account does not allow users to signup');
        }
        
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'signup')
             ;
        
        $hidden = new Zend_Form_Element_Hidden('realm');
        $hidden->setValue($realm);
        $hidden->clearDecorators();
        $hidden->addDecorators(array(
            array('ViewHelper'),    // element's view helper
        ));
            
        $form->addElement($hidden, 'realm');
        
        $realmStatic = $form->createElement('text', 'realmStatic', array('label' => 'Login Method:'));
        $realmStatic->setValue($config->authentication->$realm->name);
        $realmStatic->setAttrib('readonly', true);
        
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

        $group = array('realmStatic', 'username', 'password', 'passwordConf', 'firstName', 'lastName', 'emailAddress');
        
        $form->addElements(array($realmStatic, $username, $password, $passwordConf, $firstName, $lastName, $email));

        if (isset($config->accountPlugin)) {
            $acctPlugin = new $config->accountPlugin;
            
            $subform = $acctPlugin->addSubForm();
            
            foreach ($subform->getElements() as $e) {
                $form->addElement($e);
                $group[] = $e->getName();
            }
        }
        
        $custom = new Ot_Custom();
        
        $attributes = $custom->getAttributesForObject('Ot_Profile', 'Zend_Form');
        
        foreach ($attributes as $a) {
            $form->addElement($a['formRender']);
            $group[] = $a['formRender']->getName();
        }
                
        $form->addDisplayGroup($group, 'fields')
             ->addElement('submit', 'signupButton', array('label' => 'Sign Up Now!'))
             ->addElement('button', 'cancel', array('label' => 'Cancel'))
             ;            	
    	
        $messages = array();
    	if ($this->_request->isPost()) {
    	    if ($form->isValid($_POST)) {
    	    	
    	    	if ($form->getValue('password') == $form->getValue('passwordConf')) {
		    		$authz = new $config->authorization('nouser');
			        
		    		$userId = $form->getValue('username') . '@' . $form->getValue('realm');
		    		
			        $user = $auth->getUser($userId);
			        
			        if (count($user) == 0) {

			        	$account = new Ot_Account();
                        $data = array(
                            'userId'       => $userId,
                            'emailAddress' => $form->getValue('emailAddress'),
                            'firstName'    => ucwords($form->getValue('firstName')),
                            'lastName'     => ucwords($form->getValue('lastName'))
                        );
                        
                        $dba = Zend_Registry::get('dbAdapter');
                        $dba->beginTransaction();
                        
			        	try {
				            $password = $auth->addAccount($userId, $form->getValue('password'));
				            
				            $authz->addUser($userId, $config->loginOptions->defaultRoleOnAuthzInstanceCreation);
				            
				            $account->insert($data);
			        	} catch (Exception $e) {
			        		$dba->rollback();
			        		throw $e;
			        	}
			        	
				        if (isset($config->accountPlugin)) {
		                    $acctPlugin = new $config->accountPlugin;
		                    
		                    $subform = $acctPlugin->addSubForm();
		                    
		                    $data = array('userId' => $userId);
		                    
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

	                    $attributes = $custom->getAttributesForObject('Ot_Profile');
	        
	                    $data = array();
	                    foreach ($attributes as $a) {
	                        $data[$a['attributeId']] = $form->getValue('custom_' . $a['attributeId']);
	                    }                   
	                    
	                    try {
	                        $custom->saveData('Ot_Profile', $userId, $data);
	                    } catch (Exception $e) {
	                        $dba->rollback();
	                        throw $e;
	                    }              
			        	
			        	$dba->commit();
				        
				        $this->_flashMessenger->addMessage('Your account was created successfully.  You may now log in.');
				        
			            $this->_logger->setEventItem('attributeName', 'userId');
			            $this->_logger->setEventItem('attributeId', $userId);
			            $this->_logger->info('User Successfully signed up'); 	
			            
	                    $et = new Ot_Trigger();
	                    $et->setVariables($data);
	                    
	                    $et->password    = $password;
	                    $et->username    = preg_replace('/@.*/', '', $data['userId']);
	                    $et->loginMethod = $config->authentication->$realm->name;
	                    
	                    $et->dispatch('Login_Index_Signup');		            
			
			            $this->_helper->redirector->gotoUrl('/login/?realm=' . $realm);
			        } else {
			           $messages[] = 'User ID is taken.  Please select a different ID';
			        }
    	    	} else {
    	    		$messages[] = 'The passwords you entered do not match.';
    	    	}
    	    } else {
    	    	$messages[] = 'You did not fill in valid information into the form.';
    	    }
    	}
    	
    	$this->view->messages = $messages;
        $this->view->form = $form;
    	$this->view->title = 'Sign-up for an account';
    	$this->view->javascript = array('mooStrength.js');
    	   	
    }
}