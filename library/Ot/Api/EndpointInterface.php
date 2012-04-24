<?php
interface Ot_Api_EndpointInterface
{
    public function get($params);
    
    public function put($params);
    
    public function post($params);
    
    public function delete($params);
}
