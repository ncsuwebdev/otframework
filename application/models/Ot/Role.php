<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Role
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles ACL Roles
 *
 * @package    Ot_Role
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * 
 */
class Ot_Role extends Ot_Db_Table
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $_name = 'tbl_ot_role';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'roleId';
    
    /**
     * Cache key to store ACL results
     *
     */
    protected $_cacheKey = array(
        'application' => 'Ot_Acl_Application',
        'remote'      => 'Ot_Acl_Remote',
    );
        
    /**
     * Gets all roles in the db
     *
     * @return array
     */
    public function getRoles($scope = 'application')
    {
        $cache = Zend_Registry::get('cache');
        
        if (!$aclData = $cache->load($this->_cacheKey[$scope])) {
            
            $rule = new Ot_Role_Rule();
        
            $roles = $this->fetchAll(null, array('inheritRoleId ASC', 'roleId ASC'))->toArray();
            
            $where = $rule->getAdapter()->quoteInto('scope = ?', $scope);
            $rules = $rule->fetchAll($where, 'roleId ASC')->toArray();
    
            $aclData = array();
    
            foreach ($roles as $role) {
                $role['rules'] = array();
                foreach ($rules as $key => $rule) {
                    if ($rule['roleId'] == $role['roleId']) {
                        $role['rules'][] = $rule;
                        unset($rules[$key]);
                    }
                }
                
                $aclData[$role['roleId']] = $role;
            }
            
            $cache->save($aclData, $this->_cacheKey[$scope]);
        }
        
        return $aclData;
    }
    
    /**
     * Inserts a new role into the database 
     *
     * @param array $data
     * @return int roleId
     */
    public function insert(array $data)
    {
        $roleId = parent::insert($data);
        $this->_clearCache();
        
        return $roleId;
    }
    
    /**
     * Updates an existing role in the database 
     *
     * @param array $data
     * @param string $where
     * @return unknown
     */
    public function update(array $data, $where)
    {
        parent::update($data, $where);
        $this->_clearCache();
    }    
    
    /**
     * deletes a role from the db
     *
     * @param unknown_type $where
     */
    public function delete($where) 
    {
        parent::delete($where);
        $this->_clearCache();
    }
    
    /**
     * Assigns rules to a given role ID
     *
     * @param int $roleId
     * @param array $rules
     */
    public function assignRulesForRole($roleId, $scope, $rules)
    {
        $dba = $this->getAdapter();
            
        $inTransaction = false;
        
        try {
            $dba->beginTransaction();
        } catch (Exception $e) {
            $inTransaction = true;
        }
        
        $roleRule = new Ot_Role_Rule();
        
        $where = $dba->quoteInto('roleId = ?', $roleId)
               . ' AND '
               . $dba->quoteInto('scope = ?', $scope);
        try {
                $roleRule->delete($where);
        } catch (Exception $e) {
            if (!$inTransaction) {
                $dba->rollback();
            }
        }
            
        foreach ($rules as $rule) {
            
            $rule['roleId'] = $roleId;
            $rule['scope']  = $scope;
                
            try {
                
                $roleRule->insert($rule);
                
            } catch (Exception $e) {
                
                if (!$inTransaction) {
                    $dba->rollBack();
                }
                
                throw $e;                
            }
        }
            
        if (!$inTransaction) {
            $dba->commit();
        }
        
        $this->_clearCache();    
    }
        
    /**
     * Deletes a role from the db
     *
     * @param array $data
     * @param string $where
     * @return unknown
     */
    public function deleteRole($roleId)
    {
        $dba = $this->getAdapter();
            
        $inTransaction = false;
        
        try {
            $dba->beginTransaction();
        } catch (Exception $e) {
            $inTransaction = true;
        }
        
        $where = $dba->quoteInto('roleId = ?', $roleId);
        
        try {
            $this->delete($where);
        } catch (Exception $e) {
            if (!$inTransaction) {
                $dba->rollBack();
            }
            throw $e;                
        }
            
        $roleRule = new Ot_Role_Rule();
            
        try {
            $roleRule->delete($where);
        } catch (Exception $e) {
            if (!$inTransaction) {
                $dba->rollback();
            }
        }
        
        $accountRoles = new Ot_Account_Roles();
        try {
            $accountRoles->delete($where);
        } catch (Exception $e) {
            if (!$inTransaction) {
                $dba->rollback();
            }
        }
            
        if (!$inTransaction) {
            $dba->commit();
        }
        
        $this->_clearCache();
    }    

    /**
     * Clears the cached ACL file
     *
     */
    protected function _clearCache()
    {
        $cache = Zend_Registry::get('cache');
        foreach ($this->_cacheKey as $c) {
            $cache->remove($c);
        }
    }
    
    /**
     * Gets the form for adding and editing a ticket
     *
     * @param array $values
     * @return Zend_Form
     */
    public function form($values = array())
    {
        $form = new Zend_Form();
        $form->setAttrib('id', 'roleForm')->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array(
                        'tag' => 'div',
                        'class' => 'zend_form',
                    ),
                 ),
                 'Form',
             )
        );
             
        $name = $form->createElement('text', 'name', array('label' => 'model-role-form:roleName'));
        $name->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('StripTags')
              ->setAttrib('maxlength', '128')
              ->setValue((isset($values['name']) ? $values['name'] : ''));
              
        $inheritRoleId = $form->createElement(
            'select',
            'inheritRoleId',
            array('label' => 'model-role-form:inheritRoleId')
        );
        
        $acl = Zend_Registry::get('acl');
        $roles = $acl->getAvailableRoles();

        $inheritRoleId->addMultiOption(0, 'No Inheritance');
        foreach ($roles as $r) {
            
            if (isset($values['roleId'])) {
                
                if (!$acl->inheritsRole($r['roleId'], $values['roleId']) && $r['roleId'] != $values['roleId']) {
                    $inheritRoleId->addMultiOption($r['roleId'], $r['name']);
                }
            } else {
                $inheritRoleId->addMultiOption($r['roleId'], $r['name']);
            }
            
        }         
        
        $inheritRoleId->setValue((isset($values['inheritRoleId'])) ? $values['inheritRoleId'] : 0);


        $submit = $form->createElement('submit', 'submitButton', array('label' => 'model-role-form:submit'));
        $submit->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit'))));

        $cancel = $form->createElement('button', 'cancel', array('label' => 'form-button-cancel'));
        $cancel->setAttrib('id', 'cancel');
        $cancel->setDecorators(array(array('ViewHelper', array('helper' => 'formButton'))));

        $form->addElements(array($name, $inheritRoleId));

        $form->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'elm')),
                array('Label', array('tag' => 'span')),
            )
        )->addElements(array($submit, $cancel));

        if (isset($values['roleId'])) {

            $roleId = $form->createElement('hidden', 'roleId');
            $roleId->setValue($values['roleId']);
            $roleId->setDecorators(array(array('ViewHelper', array('helper' => 'formHidden'))));

            $form->addElement($roleId);
        }
        return $form;
    }    
}