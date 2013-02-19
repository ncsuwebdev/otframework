<?php

class Ot_Apiendpoint_MyAccount extends Ot_Api_EndpointTemplate
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
}