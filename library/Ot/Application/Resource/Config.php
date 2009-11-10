<?php
class Ot_Application_Resource_Config extends Zend_Application_Resource_ResourceAbstract
{      
    public function init()
    {
        $cache = null;
        
        if (Zend_Registry::isRegistered('cache')) {
            $cache = Zend_Registry::get('cache');
        }

        if (is_null($cache) || !$config = $cache->load('configObject')) {
            
            $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/config.xml', 'production', true);
            
            if (is_file(APPLICATION_PATH . '/../overrides/config/config.xml')) {
                
                $configOverride = new Zend_Config_Xml(APPLICATION_PATH . '/../overrides/config/config.xml', 'production');
                
                if ($configOverride instanceof Zend_Config) {
                    foreach ($configOverride->user as $key => $value) {
                        if (isset($config->user->{$key})) {
                            $config->user->{$key}->val = $value->val;
                        }
                    }
                    foreach ($configOverride->app as $key => $value) {
                        if (isset($config->app->{$key})) {
                            $config->app->{$key} = $value;
                        }
                    }
                }

            }

            if (!is_null($cache)) {
                $cache->save($config, 'configObject');
            }
        }
        
        Zend_Registry::set('config', $config);    
    }
}