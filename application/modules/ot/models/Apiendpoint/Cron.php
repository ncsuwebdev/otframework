<?php

class Ot_Model_Apiendpoint_Cron implements Ot_Api_EndpointInterface
{
    /**
     * Returns the list of cron jobs and their statuses.
     * 
     * No params required
     *
     */
    public function get($params)
    {
        $cron = new Ot_Model_DbTable_CronStatus();
        return $cron->fetchAll()->toArray();
    }
    
    /**
     * Allows a cron job to be set to enabled or disabled
     * 
     * Params
     * ===========================    
     * Required:
     *   - name: The name of the cron job
     *   - status: Either 'enabled' or 'disabled'
     *
     */
    public function put($params){
        $this->checkForEmptyParams(array('name', 'status'), $params);
        
        $status = strtolower($params['status']);
        if (!in_array($status, array('enabled', 'disabled'))) {
            throw new Ot_Exception_Data('msg-error-invalidStatus');
        }

        $cron = new Ot_Model_DbTable_CronStatus();
        return $cron->setCronStatus($params['name'], $status);
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
            if (!isset($params[$e]) || empty($params[$e])) {
                throw new Ot_Exception_ApiMissingParams('Missing required parameter:' . $e);
            }
        }
    
    }
}