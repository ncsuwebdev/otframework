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
class Account_IndexController extends Zend_Controller_Action 
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
        
        $userData = array_merge($userData, $thisAccount->toArray());
        
        $a = $config->app->authentication->{$userData['realm']};
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
            'delete'          => ($this->_helper->hasAccess('delete') && $this->_userData['accountId'] != Zend_Auth::getInstance()->getIdentity()->accountId),
            'generateApiCode' => $this->_helper->hasAccess('generate-api-code'),
            'deleteApiCode'   => $this->_helper->hasAccess('delete-api-code'),
            'changePassword'  => $this->_authAdapter->manageLocally() && 
                $this->_userData['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId &&
                $this->_helper->hasAccess('change-password'),
        );

        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->remote   = ($config->user->allowRemoteAccess->val == 1);
        $this->view->userData = $this->_userData;
        
        $this->_helper->pageTitle('account-index-index:title', array($this->_userData['firstName'], $this->_userData['lastName']));

        if (isset($config->app->accountPlugin)) {
            $acctPlugin = new $config->app->accountPlugin;
            $attributes = $acctPlugin->get($this->_userData['accountId']);
        }      

        $role = new Ot_Role();
        $thisRole = $role->find($this->_userData['role']);
        
        if (is_null($thisRole)) {
        	throw new Ot_Exception_Data('Role id not found');
        }
        
        $this->view->role = $thisRole->toArray();
        $custom = new Ot_Custom();
        
        $data = $custom->getData('Ot_Profile', $this->_userData['accountId']);
        foreach ($data as $d) {
            $attributes[$d['attribute']['label']] = $d['value'];
        }
                
        $this->view->attributes = $attributes;
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
        
        $this->_helper->pageTitle('account-index-all:title');
        $this->view->messages = $this->_helper->flashMessenger->getMessages();     
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/ot/scripts/jquery.plugin.flexigrid.pack.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/public/ot/css/jquery.plugin.flexigrid.css'); 
        
        if ($this->_request->isXmlHttpRequest()) {
        	
        	$filter = Zend_Registry::get('postFilter');
        	
        	$this->_helper->layout->disableLayout();
        	$this->_helper->viewRenderer->setNoRender();
        	
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
        			foreach ($roles as $r) {
        				if ($query == $r['name']) {
        					$query = $r['roleId'];
        					break;
        				}
        			}
        		}
        		
        		$where = $account->getAdapter()->quoteInto($qtype . ' = ?', $query);
        	}

        	
        	
        	$accounts = $account->fetchAll($where, $sortname . ' ' . $sortorder, $rp, $page * $rp);
        	        	
        	$response = array(
        		'page' => $page + 1,
        		'total' => $account->fetchAll($where)->count(),
        		'rows'  => array()
        	);
        	
			$config = Zend_Registry::get('config');
        	$realms = $config->app->authentication->toArray();
        	
        	$realmMap = array();
        	foreach ($realms as $key => $r) {
        		$realmMap[$key] = $r['name'];
        	}
                	
        	foreach ($accounts as $a) {
        		$row = array(
        			'id'   => $a->accountId,
        			'cell' => array(
        				$a->username,
        				$a->firstName, 
        				$a->lastName,
        				$realmMap[$a->realm], 
        				$roles[$a->role]['name'],       				
        			),
        		);
        		
        		$response['rows'][] = $row;
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
        
        $form = $account->form();
        
        $messages = array();
        
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
                    'role'         => $form->getValue('role'),
                );    

                $dba = Zend_Registry::get('dbAdapter');
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
                    $thisRole = $role->find($accountData['role']);
                    
                    $trigger->role = $thisRole->name;
                    $trigger->loginMethod = $config->app->authentication->{$accountData['realm']}->name;
                    
                    $authAdapter = new $config->app->authentication->{$accountData['realm']}->class;
                    
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
        
                    $this->_helper->redirector->gotoUrl('/account/index/all/'); 
                }
            } else {
                $messages[] = 'msg-error-invalidForm';
            }
        }
        
        $this->view->messages = $messages;
        $this->_helper->pageTitle('account-index-add:title');
        $this->view->form = $form;

    }   
    
    /**
     * Edits an existing user
     *
     */
    public function editAction()
    {
    	$account = new Ot_Account();
    	
    	$req = new Zend_Session_Namespace(Zend_Registry::get('siteUrl') . '_request');
    	
    	$config = Zend_Registry::get('config');

    	$form = $account->form($this->_userData);
    	
        $messages = array();

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $dba = Zend_Registry::get('dbAdapter');

                $data = array(
                	'accountId'    => $this->_userData['accountId'],
                    'firstName'    => $form->getValue('firstName'),
                    'lastName'     => $form->getValue('lastName'),
                    'emailAddress' => $form->getValue('emailAddress'),
                	'timezone'     => $form->getValue('timezone'),
                );                
                
                if ($this->_userData['accountId'] != Zend_Auth::getInstance()->getIdentity()->accountId) {
                	$data['realm']    = $form->getValue('realm');
                	$data['role']     = $form->getValue('role');
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
	                
	                $this->_helper->log(Zend_Log::INFO, 'Account was modified.', $loggerOptions);
	                
	                if (isset($req->uri) && $req->uri != '') {
			        	$uri = $req->uri;
			        	
			        	$req->unsetAll();
			        	
			         	$this->_helper->redirector->gotoUrl($uri);
			        } else {
			        	$this->_helper->flashMessenger->addMessage('msg-info-accountUpdated');
			        	
			            $this->_helper->redirector->gotoUrl('/account/?accountId=' . $this->_userData['accountId']);
			        }	                
                }
            } else {
                $messages[] = 'msg-error-invalidForm';
            }
        }

        if (isset($req->uri) && $req->uri != '') {
        	$messages[] = 'msg-info-requiredDataBeforeContinuing';
        }
        
        $this->view->messages = $messages;
        $this->view->form = $form;
        $this->_helper->pageTitle('account-index-edit:title');
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
            
            $dba = Zend_Registry::get('dbAdapter');
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
	                
	        $this->_helper->log(Zend_Log::INFO, 'Account was deleted', $loggerOptions);

            $this->_helper->flashMessenger->addMessage('msg-info-accountUpdated');
            
            $this->_helper->redirector->gotoUrl('/account/index/all/');
        }
        
        $this->view->userData = $this->_userData;
        $this->_helper->pageTitle('account-index-delete:title');
        $this->view->form = $form;
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
        
        $config   = Zend_Registry::get('config');
        $auth = new $config->app->authentication->{$thisAccount->realm}->class();
        
        if (!$auth->manageLocally()) {
            throw new Ot_Exception_Access('msg-error-authAdapterSupport');
        }
        
        $form = new Zend_Form();
        $form->setAttrib('id', 'changePassword')
	         ->setDecorators(array(
	             'FormElements',
	             array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
	             'Form',
	         ))
             ;
                         
        $oldPassword = $form->createElement('password', 'oldPassword', array('label' => 'account-index-changePassword:form:oldPassword'));
        $oldPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(6, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ;   
                    
        $newPassword = $form->createElement('password', 'newPassword', array('label' => 'account-index-changePassword:form:newPassword'));
        $newPassword->setRequired(true)
                    ->addValidator('StringLength', false, array(6, 20))
                    ->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ; 
                     
        $newPasswordConf = $form->createElement('password', 'newPasswordConf', array('label' => 'account-index-changePassword:form:newPasswordConf'));
        $newPasswordConf->setRequired(true)
                        ->addValidator('StringLength', false, array(6, 20))
                        ->addFilter('StringTrim')
                        ->addFilter('StripTags')
                        ;    
                        
        $submit = $form->createElement('submit', 'changeButton', array('label' => 'account-index-changePassword:form:submit'));
        $submit->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
                 ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formButton'))
                ));
                                        

        $form->addElements(array($oldPassword, $newPassword, $newPasswordConf))
             ->setElementDecorators(array(
               	  'ViewHelper',
                  'Errors',      
                  array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
        	      array('Label', array('tag' => 'span')),      
             ))
             ->addElements(array($submit, $cancel))
             ;         

        $messages = array();
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                
                if ($form->getValue('newPassword') != $form->getValue('newPasswordConf')) {
                    $messages[] = 'msg-error-passwordMismatch';
                }
    
                if (md5($form->getValue('oldPassword')) != $thisAccount->password) {
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
	                
                    $this->_helper->redirector->gotoUrl('/account/');
                }
            } else {
                $messages[] = 'msg-error-invalidForm';
            }
        } 
        
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/public/ot/scripts/jquery.plugin.passStrength.js');
        $this->view->messages = $messages;
        $this->_helper->pageTitle('account-index-changePassword:title');
        $this->view->form  = $form;
    }        

    /**
     * Add an API code so the user can access the application through the SOAP API
     *
     */
    public function generateApiCodeAction()
    {
        $config = Zend_Registry::get('config');

        if ($config->user->allowRemoteAccess->val != 1) {
            throw new Ot_Exception_Access('msg-error-noRemoteAccess');      
        }

        $account = new Ot_Account();
        
        $data = array(
        	'accountId' => $this->_userData['accountId'],
        	'apiCode'   => $account->generateApiCode(),
        );
        
        $account->update($data, null);
		$loggerOptions = array(
	       	'attributeName' => 'accountId',
	       	'attributeId'   => $this->_userData['accountId'],
	    );
	                
	    $this->_helper->log(Zend_Log::INFO, 'API Code generated', $loggerOptions);
                
        $this->_helper->flashMessenger->addMessage('msg-info-apiCodeCreated');
        
        $this->_helper->redirector->gotoUrl('/account/?accountId=' . $this->_userData['accountId']);       
    }

    /**
     * Deletes an API code to remove the users remote access
     *
     */
    public function deleteApiCodeAction()
    {
		$form = Ot_Form_Template::delete('deleteApiCode');                 
       
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            $account = new Ot_Account();
            
            $data = array(
            	'accountId' => $this->_userData['accountId'],
            	'apiCode'   => '',
            );
            
            $account->update($data, null);
            
			$loggerOptions = array(
	           	'attributeName' => 'accountId',
	          	'attributeId'   => $this->_userData['accountId'],
	        );
	                
	        $this->_helper->log(Zend_Log::INFO, 'API Code deleted.', $loggerOptions);

            $this->_helper->flashMessenger->addMessage('msg-info-apiCodeDeleted');
            
            $this->_helper->redirector->gotoUrl('/account/?accountId=' . $this->_userData['accountId']);

        }
        
        $this->view->userData = $this->_userData;
        $this->_helper->pageTitle('account-index-deleteApiCode:title');
        $this->view->form = $form;
    }       

    /**
     * Allows a user to change their role and others
     *
     */
    public function changeUserRoleAction()
    {}
    
    /**
     * Allows a user to edit all user accounts in the system
     *
     */
    public function editAllAccountsAction()
    {}
}