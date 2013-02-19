<?php
class Ot_Apiendpoint_Version extends Ot_Api_EndpointTemplate
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
            'Application'   => Ot_Application_Version::getVersion(),
        );
    }
}