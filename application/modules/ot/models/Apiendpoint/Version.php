<?php
class Ot_Model_Apiendpoint_Version implements Ot_Api_EndpointInterface
{
    /**
     * Returns the versions of OT Framework and Zend Framework used in the app.
     * 
     * No params required
     *
     */
    public function get($params)
    {
        return array(
            'OTFramework'   => Ot_Version::VERSION,
            'ZendFramework' => Zend_Version::VERSION,
            'Application'   => Zend_Registry::get('applicationVersion')
        );
    }
    
    /**
     * Unavailable
     */
    public function put($params){
        throw new Ot_Exception_ApiEndpointUnavailable('PUT is unavailable for this endpoint');
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
}