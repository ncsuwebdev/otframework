<?php
class Ot_Api_EndpointTemplate
{
    public function get($params)
    {
        throw new Ot_Exception_ApiEndpointUnavailable('GET is unavailable for this endpoint');
    }
    
    public function put($params)
    {
        throw new Ot_Exception_ApiEndpointUnavailable('PUT is unavailable for this endpoint');
    }
    
    public function post($params)
    {
        throw new Ot_Exception_ApiEndpointUnavailable('POST is unavailable for this endpoint');
    }
    
    public function delete($params)
    {
        throw new Ot_Exception_ApiEndpointUnavailable('DELETE is unavailable for this endpoint');
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
