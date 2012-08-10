<?php

class Ot_Model_Apiendpoint_MyAccount implements Ot_Api_EndpointInterface
{
 
    /**
     * Returns the account information for the current user (the user associated
     * with the API key used)
     * 
     * No params required
     *
     */
    public function get($params)
    {
       $otAccount = new Ot_Model_DbTable_Account();
       
       if (!Zend_Auth::getInstance()->hasIdentity()) {
           throw new Ot_Exception_Access('msg-error-apiAccessDenied');
       }
       
       $accountId = Zend_Auth::getInstance()->getIdentity()->accountId;
       
       $accountInfo = $otAccount->find($accountId);
       
       if (is_null($accountInfo)) {
          throw new Ot_Exception_Data('msg-error-noAccount');
       }
       
       unset($accountInfo->password);
       unset($accountInfo->role);
       
       return $accountInfo;
    }

    /**
     * Updates the currently logged in user's account information (the user 
     * associated with the API key)
     * 
     * Params
     * ===========================    
     * Required:
     *   - firstName: The first name of the user
     *   - lastName: The last name of the user
     *   - emailAddress: The email address of the user
     *   - timezone: The timezone of the user (America/New_York, etc.)
     *
     */
    public function put($params){
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            throw new Ot_Exception_Access('msg-error-apiAccessDenied');
        }
        
        $this->checkForEmptyParams(array('firstName', 'lastName', 'emailAddress', 'timezone'), $params);
        
        if (!in_array($params['timezone'], Ot_Model_Timezone::getTimezoneList())) {
            throw new Ot_Exception_Data('msg-error-invalidTimezone');
        }
        
        $otAccount = new Ot_Model_DbTable_Account();
        
        $accountId = Zend_Auth::getInstance()->getIdentity()->accountId;
        
        $data = array(
                    'accountId'    => $accountId,
                    'firstName'    => $params['firstName'],
                    'lastName'     => $params['lastName'],
                    'emailAddress' => $params['emailAddress'],
                    'timezone'     => $params['timezone']
                );
                     
        $otAccount->update($data, null);
        
        return true;
    }

    /**
     * Unavailable
     */
    public function delete($params){
        throw new Ot_Exception_ApiEndpointUnavailable('DELETE is unavailable for this endpoint');
    }

    /**
     * Unavailable
     */
    public function post($params){
        throw new Ot_Exception_ApiEndpointUnavailable('POST is unavailable for this endpoint');
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
            if (!isset($params[$e]) || empty($params[$e])) {
                throw new Ot_Exception_ApiMissingParams('Missing required parameter:' . $e);
            }
        }
    
    }
}