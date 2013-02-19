<?php
class Ot_Apiendpoint_Account extends Ot_Api_EndpointTemplate
{
    
    /**
     * Gets the account information for a user.  You can either lookup a user
     * by accountId or by username and realm.
     *  
     * Params
     * ===========================    
     * Required:
     *   - accountId: The account ID of the user to lookup
     *   OR
     *   - username: The username to lookup
     *   - realm: The realm of the username (wrap, local, etc.)
     * 
     */
    public function get($params)
    {  
        if (!isset($params['accountId']) && (!isset($params['username']) && !isset($params['realm']))) {
            throw new Ot_Exception_Input('You must provide either an account ID or a username and realm.');
        }
        
        $otAccount = new Ot_Model_DbTable_Account();
        
        if (isset($params['accountId'])) {
        
            $accountId = $params['accountId'];
            $accountInfo = (array)$otAccount->find($accountId);
            
        } else {
            
            $username = trim($params['username']);
            $realm = trim($params['realm']);
            
            $where = $otAccount->getAdapter()->quoteInto('username = ?', $username);
            $where .= ' AND ' . $otAccount->getAdapter()->quoteInto('realm = ?', $realm);
            
            $accountInfo = $otAccount->fetchRow($where)->toArray();
        }

        if (is_null($accountInfo) || empty($accountInfo)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }
        
        unset($accountInfo['password']);
        unset($accountInfo['role']);
        return $accountInfo;
    }
    
    /**
     * Updates a user's account information.
     * 
     * Params
     * ===========================    
     * Required:
     *   - accountId: The accountId of the user to update
     *   - firstName: The first name of the user
     *   - lastName: The last name of the user
     *   - emailAddress: The email address of the user
     *   - timezone: The timezone of the user (America/New_York, etc.)
     *
     */
    public function put($params) {

        $expectedParams = array(
                'accountId',
                'firstName',
                'lastName',
                'emailAddress',
                'timezone',
            );
        
        $this->checkForEmptyParams($expectedParams, $params);
        
        if (!in_array($params['timezone'], Ot_Model_Timezone::getTimezoneList())) {
            throw new Ot_Exception_Data('msg-error-invalidTimezone');
        }
        
        $otAccount = new Ot_Model_DbTable_Account();
        
        $data = array(
            'accountId'    => $params['accountId'],
            'firstName'    => $params['firstName'],
            'lastName'     => $params['lastName'],
            'emailAddress' => $params['emailAddress'],
            'timezone'     => $params['timezone']
        );
        
        $otAccount->update($data, null);
        return true;
        
    }    
    
    /**
     * Delete a user's account completely.  You cannot delete your own account.
     * 
     * Params
     * ===========================    
     * Required:
     *   - accountId: The accountId of the user to update
     *
     */
    public function delete($params){
        
        if (!isset($params['accountId'])) {
            throw new Ot_Exception_Input('You must provide an account ID.');
        }
        
        $otAccount = new Ot_Model_DbTable_Account();
        
        $accountId = trim($params['accountId']);
        $accountInfo = $otAccount->find($accountId);
        
        if (is_null($accountInfo)) {
            throw new Ot_Exception_Data('msg-error-noAccount');
        }
        
        if ($accountId == Zend_Auth::getInstance()->getIdentity()->accountId) {
            throw new Ot_Exception_Data('You cannot delete your own account');
        }
        
        $where = $otAccount->getAdapter()->quoteInto('accountId = ?', $accountId);
        
        try {
            $deleteCount = $otAccount->delete($where);
        } catch (Exception $e) {
            throw new Ot_Exception_Data('There was an error deleting the user account. ' . $e->getMessage());
        }
              
        return array('msg' => 'Account successfully deleted');
    }
}