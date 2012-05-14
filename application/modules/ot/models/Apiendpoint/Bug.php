<?php

class Ot_Model_Apiendpoint_Bug implements Ot_Api_EndpointInterface
{
    
    public function get($params)
    {
        $otBug = new Ot_Model_DbTable_Bug();
        $status = isset($params['status']) ? $params['status'] : null;
        
        if (!is_null($status) && !in_array(strtolower($status), array('new', 'ignore', 'escalated', 'fixed'))) {
            throw new Ot_Exception_Data('msg-error-invalidStatus');
        }
        
        if (!is_null($status)) {
            $where = $otBug->getAdapter()->quoteInto('status = ?', strtolower($status));
        } else {
            $where = null;
        }
        
        $bugs = $otBug->fetchAll($where, 'submitDt DESC')->toArray();

        $bugText   = new Ot_Model_DbTable_BugText();
        $otAccount = new Ot_Model_DbTable_Account();
        
        foreach ($bugs as &$b) {
            
            $text = $bugText->getBugText($b['bugId'])->toArray();

            foreach ($text as &$t) {
                $accountInfo = $otAccount->find($t['accountId']);
                
                $t['userInfo'] = array(
                                    'accountId'    => $accountInfo->accountId,
                                    'username'     => $accountInfo->username,
                                    'realm'        => $accountInfo->realm,
                                    'firstName'    => $accountInfo->firstName,
                                    'lastName'     => $accountInfo->lastName,
                                    'emailAddress' => $accountInfo->emailAddress
                                 );
                $b['text'] = $t;
            }
            
        }
        
        return $bugs;
    }

    public function put($params){
        throw new Ot_Exception_ApiEndpointUnavailable('PUT is unavailable for this endpoint');
    }

    public function delete($params){
        throw new Ot_Exception_ApiEndpointUnavailable('DELETE is unavailable for this endpoint');
    }

    public function post($params){
        throw new Ot_Exception_ApiEndpointUnavailable('POST is unavailable for this endpoint');
    }
}