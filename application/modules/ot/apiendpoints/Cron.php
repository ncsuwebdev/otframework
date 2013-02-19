<?php

class Ot_Apiendpoint_Cron extends Ot_Api_EndpointTemplate
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
}