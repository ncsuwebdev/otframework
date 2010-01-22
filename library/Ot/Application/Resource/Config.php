<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Application_Resource_Config
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 *
 * @package   Ot_Application_Resource_Config
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */

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
                
                $configOverride = new Zend_Config_Xml(APPLICATION_PATH
                                . '/../overrides/config/config.xml', 'production');
                
                if ($configOverride instanceof Zend_Config) {
                    
                    if ($configOverride->user instanceof Zend_Config) {
                        foreach ($configOverride->user as $key => $value) {
                            if (isset($config->user->{$key})) {
                                $config->user->{$key}->val = $value->val;
                            }
                        }
                    }

                    if ($configOverride->app instanceof Zend_Config) {
                        foreach ($configOverride->app as $key => $value) {
                            if (isset($config->app->{$key})) {
                                $config->app->{$key} = $value;
                            }
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