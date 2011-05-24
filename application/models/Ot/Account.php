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
 * @package    Ot_Account
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with user profiles
 *
 * @package    Ot_Account
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Account extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_account';
    
    /**
     * The minimum length for a password
     */
    protected $_minPasswordLength = 5;
    
    /**
     * The maximum length for a generated password
     */
    protected $_maxPasswordLength = 20;

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'accountId';
    
    /**
     * Formats the resultset returned by the database
     * 
     * @param unknown_type $data
     */
    private function _addExtraData($data) {
    	
		if (get_Class($data) == 'Zend_Db_Table_Row') {
    		$data = (object) $data->toArray();
		}
		
    	$rolesDb = new Ot_Account_Roles();
    	
    	$where = $rolesDb->getAdapter()->quoteInto('accountId = ?', $data->accountId);
    	
    	$roles = $rolesDb->fetchAll($where);
    	
    	$roleList = array();
    	foreach ($roles as $r) {
			$roleList[] = $r['roleId'];
    	}
    	
    	$data->role = $roleList; 

    	return $data;   
    }
    
   	public function fetchAll($where = null, $order = null, $count = null, $offset = null) {
   		
   		try {
   			$result = parent::fetchAll($where, $order, $count, $offset);
   		} catch (Exception $e) {
   			throw $e;
   		}

   		if($result->count() > 0) {
	   		foreach ($result as $r) {
	   			$ret[] = $this->_addExtraData($r);
	   		}

   			return $ret;
   		} else {
   			return null;
   		}
   	}
   	
   	public function find() {
   		$result = parent::find(func_get_args());
   		
   		return $this->_addExtraData($result);
   	}
   	
    public function getAccount($username, $realm)
    {
        $where = $this->getAdapter()->quoteInto('username = ?', $username)
               . ' AND '
               . $this->getAdapter()->quoteInto('realm = ?', $realm);
               
        $result = $this->fetchAll($where);
        
        if (count($result) != 1) {
            return null;
        }
        return $result[0];
    }
    
    
    public function generatePassword()
    {
        return substr(md5(microtime()), 2, 2 + $this->_minPasswordLength);
    }
    
    public function generateApiCode()
    {
        return md5(microtime() * 34);
    }

    public function verify($accessCode)
    {
        $where = $this->getAdapter()->quoteInto('apiCode = ?', $accessCode);
        $this->_messages[] = $where;
        $result = $this->fetchAll($where, null, 1);
        
        if ($result->count() != 1) {
            throw new Exception('Code not found');
        }
        
        return $result->current();
    }    
    
    public function getAccountsForRole($roleId)
    {
    	$rolesDb = new Ot_Account_Roles();
    	
        $where = $rolesDb->getAdapter()->quoteInto('role = ?', $roleId);
        
        $roles = $rolesDb->fetchAll($where);
        
        $accountIds = array();
        
        foreach ($roles as $role) {
        	$accountIds[] = $roles['accountId'];
        }
        
        if(count($accountIds) > 0) {
        	$where = $this->getAdapter()->quoteInto('accountId IN (?)', $accountIds);
        	return $this->fetchAll($where);
        } else {
        	return null;
        }
    }
    
    public function form($default = array(), $signup = false) 
    {
        $config = Zend_Registry::get('config');
        $acl    = Zend_Registry::get('acl');
        
        $form = new Zend_Form();
        $form->setAttrib('id', 'account')->setDecorators(
            array('FormElements', array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')), 'Form')
        );

        $authAdapter = new Ot_Auth_Adapter;
        $adapters    = $authAdapter->fetchAll(null, 'displayOrder');
        
        // Realm Select box
        $realmSelect = $form->createElement('select', 'realm', array('label' => 'Login Method'));
        foreach ($adapters as $adapter) {
            $realmSelect->addMultiOption(
                $adapter->adapterKey,
                $adapter->name . (!$adapter->enabled ? ' (Disabled)' : '')
            );
        }
        $realmSelect->setValue((isset($default['realm'])) ? $default['realm'] : '');         
        
        // Create and configure username element:
        $username = $form->createElement('text', 'username', array('label' => 'model-account-username'));
        $username->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('Alnum')
                 ->addFilter('StripTags')
                 ->setAttrib('maxlength', '64')
                 ->setValue((isset($default['username'])) ? $default['username'] : ''); 

        // First Name
        $firstName = $form->createElement('text', 'firstName', array('label' => 'model-account-firstName'));
        $firstName->setRequired(true)
                  ->addFilter('StringToLower')
                  ->addFilter('StringTrim')
                  ->addFilter('StripTags')
                  ->addFilter(new Ot_Filter_Ucwords())
                  ->setAttrib('maxlength', '64')
                  ->setValue((isset($default['firstName'])) ? $default['firstName'] : '');

        // Last Name
        $lastName = $form->createElement('text', 'lastName', array('label' => 'model-account-lastName'));
        $lastName->setRequired(true)
                 ->addFilter('StringTrim')
                 ->addFilter('StringToLower')
                 ->addFilter('StripTags')
                  ->addFilter(new Ot_Filter_Ucwords())
                 ->setAttrib('maxlength', '64')
                 ->setValue((isset($default['lastName'])) ? $default['lastName'] : '');

        // Password field
        $password = $form->createElement('password', 'password', array('label' => 'model-account-password'));
        $password->setRequired(true)
                 ->addValidator('StringLength', false, array($this->_minPasswordLength, $this->_maxPasswordLength))
                 ->addFilter('StringTrim')
                 ->addFilter('StripTags');   

        // Password confirmation field
        $passwordConf = $form->createElement('password', 'passwordConf', array('label' => 'model-account-passwordConf'));
        $passwordConf->setRequired(true)
                     ->addValidator('StringLength', false, array($this->_minPasswordLength, $this->_maxPasswordLength))
                     ->addFilter('StringTrim')
                     ->addFilter('StripTags'); 
                                      
        // Email address field
        $email = $form->createElement('text', 'emailAddress', array('label' => 'model-account-emailAddress'));
        $email->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress')
              ->setValue((isset($default['emailAddress'])) ? $default['emailAddress'] : '');
        
        $timezone = $form->createElement('select', 'timezone', array('label' => 'model-account-timezone'));
        $timezone->addMultiOptions(Ot_Timezone::getTimezoneList());
        $timezone->setValue(
            (
                isset($default['timezone'])
                && $default['timezone'] != ''
            )
            ? $default['timezone'] : date_default_timezone_get()
        ); 
        
        // Role select box
        $roleSelect = $form->createElement('multiCheckbox', 'roleSelect');
        $roleSelect->setRequired(true);
    
        $roles = $acl->getAvailableRoles();  
           
        foreach ($roles as $r) {
            $roleSelect->addMultiOption($r['roleId'], $r['name']);
        }
        
        $roleSelect->setValue((isset($default['role'])) ? $default['role'] : '');
        
        if ($signup) {
            $form->addElements(array($username, $password, $passwordConf, $firstName, $lastName, $email, $timezone));
        } else {
            $me = false;
            
            if (isset($default['accountId'])
                && $default['accountId'] == Zend_Auth::getInstance()->getIdentity()->accountId) {
                $me = true;
            }
            
            if (!$me) {
                $form->addElements(array($realmSelect, $username));
            }
            
            $form->addElements(array($firstName, $lastName, $email, $timezone));
            
            if (!$me) {
                $form->addElement($roleSelect);
            }
        }
        
        if (isset($config->app->accountPlugin)) {
            $acctPlugin = new $config->app->accountPlugin;
            
            if (isset($default['accountId'])) {
                $subform = $acctPlugin->editSubForm($default['accountId']);
            } else {
                $subform = $acctPlugin->addSubForm();
            }
            
            foreach ($subform->getElements() as $e) {
                $form->addElement($e);
            }
        }
        
        $custom = new Ot_Custom();
        
        if (isset($default['accountId'])) {
            $attributes = $custom->getData('Ot_Profile', $default['accountId'], 'Zend_Form');
        } else {
            $attributes = $custom->getAttributesForObject('Ot_Profile', 'Zend_Form');
        }
        
        foreach ($attributes as $a) {
            $form->addElement($a['formRender']);
        }
        
        $submit = $form->createElement('submit', 'submit', array('label' => 'form-button-save'));
        $submit->setDecorators(
            array(
                array('ViewHelper', array('helper' => 'formSubmit'))
            )
        );
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setDecorators(
            array(
                array('ViewHelper', array('helper' => 'formButton'))
            )
        );
        
        $form->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',      
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
                array('Label', array('tag' => 'span')),   
            )
        )->addElements(array($submit, $cancel));
        
        /*
         * Groups the form elements into divs (general and access role)
         */
        $form->addDisplayGroup(array(
        	'submit',
        	'cancel'
        ), 'buttons');
        
        $buttons = $form->getDisplayGroup('buttons');
        $buttons->setDecorators(array(
        	'FormElements',
        	'Fieldset',
        	array('HtmlTag', array('tag' => 'div', 'class' => 'buttons'))
        ));
        
        $form->addDisplayGroup(array(
            	'realm',
            	'username',
            	'firstName',
            	'lastName',
            	'emailAddress',
            	'timezone'            	
            ), 'general', array('legend' => 'General Information'));
            
        $general = $form->getDisplayGroup('general');
        $general->setDecorators(array(
	        'FormElements',
	        'Fieldset',
	       	array('HtmlTag', array('tag' => 'div', 'class' => 'general'))
        ));
        
        $form->addDisplayGroup(array('roleSelect'), 'roles', array('legend' => 'User Access Roles'));
        $role = $form->getDisplayGroup('roles');
        $role->setDecorators(array(
        	'FormElements',
        	'Fieldset',
        	array('HtmlTag', array('tag' => 'div', 'class' => 'accessRoles'))
        ));
              
        if (isset($default['accountId'])) {
            $accountId = $form->createElement('hidden', 'accountId');
            $accountId->setValue($default['accountId']);
            $accountId->setDecorators(
                array(
                    array('ViewHelper', array('helper' => 'formHidden'))
                )
            ); 
            
            $form->addElement($accountId);
        }     

        if ($signup) {
            
            // Realm hidden box
            $realmHidden = $form->createElement('hidden', 'realm');
            $realmHidden->setValue($default['realm']);
            $realmHidden->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));             
            
            $form->addElement($realmHidden);
        }
                      
        return $form;
    }
}