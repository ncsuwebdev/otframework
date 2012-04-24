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
    
    public function put($params){}
    
    public function delete($params){}
    
    public function post($params){}
}