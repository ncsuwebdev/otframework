<?php

class Ot_Model_Apiendpoint_MyAccount implements Ot_Api_EndpointInterface
{
    
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

    public function delete($params){
        throw new Ot_Exception_ApiEndpointUnavailable('DELETE is unavailable for this endpoint');
    }

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
            if (!isset($params[$e])) {
                throw new Ot_Exception_ApiMissingParams('Missing required parameter:' . $e);
            }
        }
    
    }
}