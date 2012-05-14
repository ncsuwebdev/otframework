<?php
class Ot_Model_Apiendpoint_Account implements Ot_Api_EndpointInterface
{
    
    public function get($params)
    {
        
        if (!isset($params['accountId'])) {
            throw new Ot_Exception_Input('No account ID provided');
        }
        
        $accountId = $params['accountId'];
        
        $otAccount = new Ot_Model_DbTable_Account();
        
        $accountInfo = $otAccount->find($accountId);
        
        if (is_null($accountInfo)) {
           throw new Ot_Exception_Data('msg-error-noAccount');
        }
        
        $accountInfo->password = '';
        unset($accountInfo->password);
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
        throw new Ot_Exception_ApiEndpointUnavailable('DELETE is unavailable for this endpoint');
    }
    
    public function post($params){
        throw new Ot_Exception_ApiEndpointUnavailable('POST is unavailable for this endpoint');
    }
}