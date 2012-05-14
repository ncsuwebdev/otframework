<?php
class Ot_Model_Apiendpoint_Version implements Ot_Api_EndpointInterface
{
    
    public function get($params)
    {
        return array(
            'OTFramework'   => Ot_Version::VERSION,
            'ZendFramework' => Zend_Version::VERSION,
        );
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