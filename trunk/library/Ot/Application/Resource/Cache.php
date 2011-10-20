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
 * @package    Ot_Application_Resource_Cache
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 *
 * @package   Ot_Application_Resource_Cache
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Application_Resource_Cache extends Zend_Application_Resource_ResourceAbstract
{   
    protected $_caching = false;
    
    public function setCaching($caching)
    {
        $this->_caching = (bool)$caching;
    }
    
    public function init()
    {        
        $this->getBootstrap()->bootstrap('db');
        
        $frontendOptions = array(
            'lifetime'                => 86400, // cache lifetime of 24 hours
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/../cache',
        );
        
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        $cache->setOption('caching', $this->_caching);
        
        Zend_Db_Table::setDefaultMetadataCache($cache); 
        
        Zend_Registry::set('cache', $cache);
    }
}