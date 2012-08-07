<?php
class Ot_Model_Apiendpoint_Account implements Ot_Api_EndpointInterface
{
    
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
    
    public function put($params){

        $expectedParams = array(
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
     * Helper class that compares an array of expected parameters and the received list.
     * Throws an Ot_Exception_Data if a parameter is missing.
     *
     * @param array $expected
     * @param array $params
     * @throws Ot_Exception_Data
     */
    protected function checkForEmptyParams(array $expected, array $params) {
    
        foreach ($expected as $e) {
            if (!isset($params[$e])) {
                throw new Ot_Exception_ApiMissingParams('Missing required parameter:' . $e);
            }
        }
    }
    
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
    
    public function post($params){
        throw new Ot_Exception_ApiEndpointUnavailable('POST is unavailable for this endpoint');
    }
}