<?php
class Ot_Application_Resource_Cache extends Zend_Application_Resource_ResourceAbstract
{   
    protected $_caching = false;
    
    public function setCaching($caching)
    {
        $this->_caching = (bool)$caching;
    }
    
    public function init()
    {
        $frontendOptions = array(
            'lifetime'                => 21600, // cache lifetime of 6 hours
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/../cache',
        );
        
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        $cache->setOption('caching', $this->_caching);
        
        Zend_Registry::set('cache', $cache);        
    }
}