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

        if(empty($data)) {
            return $data;
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


       public function insert(array $data)
       {
           $roleIds = array();
           if(isset($data['role']) && count($data['role']) > 0) {
               $roleIds = (array)$data['role'];
               unset($data['role']);
           }
           try {
               $accountId = parent::insert($data);
           } catch(Exception $e) {
               throw new Ot_Exception('Account insert failed.');
           }
           $a = new Ot_Account_Roles();
           if(count($roleIds) > 0) {
               $accountRoles = new Ot_Account_Roles();

               foreach($roleIds as $r) {
                   $accountRoles->insert(array(
                       'accountId' => $accountId,
                       'roleId'    => $r,
                   ));
               }
           }
           return $accountId;
       }

       public function update(array $data, $where)
       {
           $rolesToAdd = array();
           if(isset($data['role']) && count($data['role']) > 0) {
               $rolesToAdd = (array)$data['role'];
               unset($data['role']);
           }
           $updateCount = parent::update($data, $where);
           if(count($rolesToAdd) < 1) {
               return $updateCount;
           }
           $accountRoles = new Ot_Account_Roles();
           $accountRolesDba = $accountRoles->getAdapter();

           $accountId = $data['accountId'];

           if(isset($rolesToAdd) && count($rolesToAdd) > 0) {
               try {
                   $where = $accountRolesDba->quoteInto('accountId = ?', $accountId);
                   $accountRoles->delete($where);
                   foreach($rolesToAdd as $roleId) {
                       $d = array(
                           'accountId' => $accountId,
                           'roleId' => $roleId,
                       );
                       $accountRoles->insert($d);
                   }
               } catch(Exception $e) {
                   throw $e;
               }
           }
           return $updateCount;
       }

       public function delete($where)
       {
           $deleteCount = parent::delete($where);
           $accountRoles = new Ot_Account_Roles();
           $accountRoles->delete($where);
           return $deleteCount;
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

        if (count($result) != 1) {
            throw new Exception('Code not found');
        }

        return $result->current();
    }

    public function getAccountsForRole($roleId, $order = null, $count = null, $offset = null)
    {
        $rolesDb = new Ot_Account_Roles();

        $where = $rolesDb->getAdapter()->quoteInto('roleId = ?', $roleId);

        $roles = $rolesDb->fetchAll($where)->toArray();
        $accountIds = array();
        foreach ($roles as $role) {
            $accountIds[] = $role['accountId'];
        }

        if(count($accountIds) > 0) {
            $where = $this->getAdapter()->quoteInto('accountId IN (?)', $accountIds);
            return $this->fetchAll($where, $order, $count, $offset);
        } else {
            return null;
        }
    }
    
    
    public function changeAccountRoleForUnityId($unityId, $newRoleId)
    {
        $where = $this->getAdapter()->quoteInto('username = ?', $unityId);
        $where .= $this->getAdapter()->quoteInto(' AND realm = ?', 'wrap');
        $thisAccount = $this->fetchAll($where)->toArray();
        
        $dba = $this->getAdapter();
        $dba->beginTransaction();
        if(count($thisAccount) == 1) {
                $data = array(
                    'accountId' => $thisAccount[0]['accountId'],
                       'role'      => $newRoleId
                );
                                
                try {
                        $this->update($data, null);
                        $dba->commit();
                        $dba->closeConnection();
                        return true;
                } catch (Exception $e) {
                        $dba->rollback();
                        $dba->closeConnection();
                        return false;
                }
        } else {
            $dba->closeConnection();
            return false;
        }
        
    }
    
    public function createNewUserForUnityId($unityId, $roleId)
    {    
        $account = new Ot_Account();
        $where = $account->getAdapter()->quoteInto('username = ?', $unityId);
        $where .= $account->getAdapter()->quoteInto(' AND realm = ?', 'wrap');
        $thisAccount = $account->fetchAll($where)->toArray();
        
        $dba = $account->getAdapter();
        $dba->beginTransaction();
        
        if (count($thisAccount) < 1) {
            $data = array (
                'username'  => $unityId, 
                'realm'     => 'wrap', 
                'timezone'  => 'America/New_York', 
                'role'      => $roleId,
            );

            try {
                $account->insert($data, null);
                $dba->commit();
                $dba->closeConnection();
                return true;
            } catch (Exception $e) {
                $dba->rollback();
                $dba->closeConnection();
                return false;
            }
        } else {
            $dba->closeConnection();
            return false;
        }
    }
    
    public function importForm(array $default = array())
    {
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'noteForm')
             ->setDecorators(array(
                 'FormElements',
                 array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                 'Form',
             ));
                                               
        $text = $form->createElement('textarea', 'text', array('label' => 'Enter a comma separated list of unity IDs:'));
        $text->addFilter('StringTrim')
              ->setAttrib('id', 'wysiwyg')
              ->setAttrib('style', 'width: 650px; height: 200px;')
              ->setValue((isset($default['text'])) ? $default['text'] : 'userid,userid2,userid3');
        
        $roleList = array();
        $otRole = new Ot_Role();
        $allRoles = $otRole->fetchAll();
        foreach ($allRoles as $r) {
            $roleList[$r->roleId] = $r->name;
        }
             
        $newRoleId = $form->createElement('radio', 'newRoleId', array('label' => 'Choose a role for all accounts listed above: '));
        $newRoleId->setRequired(true);
        $newRoleId->setMultiOptions($roleList);
        $newRoleId->setValue((isset($default['newRoleId'])) ? $default['newRoleId'] : '');
              
    $form->addElements(array($text, $newRoleId));
        
        $submit = $form->createElement('submit', 'saveButton', array('label' => 'Submit'));
        $submit->setDecorators(array(
            array('ViewHelper', array('helper' => 'formSubmit'))
        ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
            array('ViewHelper', array('helper' => 'formButton'))
        ));        
                            
        $form->setElementDecorators(array(
                 'ViewHelper',
                 'Errors',      
                 array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
                 array('Label', array('tag' => 'span')),      
             ))->addElements(array($submit, $cancel));
             
        return $form;
    }

    public function changeRoleForm(array $default = array())
    {
        $form = new Zend_Form();
        $form->setAction('')
             ->setMethod('post')
             ->setAttrib('id', 'noteForm')
             ->setDecorators(array(
                 'FormElements',
                 array('HtmlTag', array('tag' => 'div', 'class' => 'zend_form')),
                 'Form',
             ));
                                               
        $text = $form->createElement('textarea', 'text', array('label' => ' Enter a comma separated list of unity IDs:'));
        $text->addFilter('StringTrim')
             ->setAttrib('id', 'wysiwyg')
             ->setAttrib('style', 'width: 650px; height: 200px;')
             ->setValue((isset($default['text'])) ? $default['text'] : 'userid,userid2,userid3');
        
        $roleList = array();
        $otRole = new Ot_Role();
        $allRoles = $otRole->fetchAll();
        foreach ($allRoles as $r) {
            $roleList[$r->roleId] = $r->name;
        }
             
        $newRoleId = $form->createElement('radio', 'newRoleId', array('label' => 'Choose new role for the accounts listed above: '));
        $newRoleId->setRequired(true);
        $newRoleId->setMultiOptions($roleList);
        $newRoleId->setValue((isset($default['newRoleId'])) ? $default['newRoleId'] : '');
              
        $form->addElements(array($text, $newRoleId));
        
        $submit = $form->createElement('submit', 'saveButton', array('label' => 'Submit'));
        $submit->setDecorators(array(
            array('ViewHelper', array('helper' => 'formSubmit'))
        ));
        
        $cancel = $form->createElement('button', 'cancel', array('label' => 'Cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(
               array('ViewHelper', array('helper' => 'formButton'))
        ));        
                            
        $form->setElementDecorators(array(
             'ViewHelper',
             'Errors',      
             array('HtmlTag', array('tag' => 'div', 'class' => 'elm')), 
             array('Label', array('tag' => 'span')),      
         ))->addElements(array($submit, $cancel));
             
        return $form;
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
                 ->addValidator('StringLength', false, array(3, 64))
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
                     ->addValidator('Identical', false, array('token' => 'password'))
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

        $roleSelect->setAttrib('class', 'roleSelect');

        if ($signup) {
            $form->addElements(array($username, $password, $passwordConf, $firstName, $lastName, $email, $timezone));
        } else {
            $me = false; // bool value for if you're trying to edit your own account
            // Is this even necessary? Someone that can edit account probably is a super admin,
            // so why restrict this? They could just create a new user, change their permissions,
            // then log in as the new account to switch their main account's permissions.

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

        $subformElements = array();

        if (isset($config->app->accountPlugin)) {
            $acctPlugin = new $config->app->accountPlugin;

            if (isset($default['accountId'])) {
                $subform = $acctPlugin->editSubForm($default['accountId']);
            } else {
                $subform = $acctPlugin->addSubForm();
            }

            foreach ($subform->getElements() as $e) {
                $form->addElement($e);
                $subformElements[] = $e->getName();
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


        if(!$signup) {
            $form->addDisplayGroup(array_merge(array(
                    'realm',
                    'username',
                    'firstName',
                    'lastName',
                    'emailAddress',
                    'timezone'), $subformElements, array(
                        'submit',
                        'cancel'))
                , 'general', array('legend' => 'General Information'));

            $general = $form->getDisplayGroup('general');
            $general->setDecorators(array(
                'FormElements',
                'Fieldset',
                   array('HtmlTag', array('tag' => 'div', 'class' => 'general'))
            ));
        }


        if(!$signup && !$me) {
            $form->addDisplayGroup(array('roleSelect'), 'roles', array('legend' => 'User Access Roles'));
            $role = $form->getDisplayGroup('roles');
            $role->setDecorators(array(
                'FormElements',
                'Fieldset',
                array('HtmlTag', array('tag' => 'div', 'class' => 'accessRoles'))
            ));
        }

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