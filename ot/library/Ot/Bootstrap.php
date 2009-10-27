<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Bootstrap
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Main OT Bootstrap file, which is setup as a Singleton pattern.  It allows
 * consistant initilization of all pieces of our Application Framework.
 *
 * @package    OT_Bootstrap
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Ot_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{    
    protected function _initFc()
    {
        $fc = Zend_Controller_Front::getInstance();
        
        $fc->addModuleDirectory(APPLICATION_PATH . '/modules');
        $fc->addModuleDirectory(OT_APPLICATION_PATH . '/modules');
        $eHandlerConfig = array(
            'module'     => 'error',
            'controller' => 'error',
            'action'     => 'error',
        );

        $fc->registerPlugin(new Zend_Controller_Plugin_ErrorHandler($eHandlerConfig));
        
    }
    
    protected function _initAutoload()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
    }

    
    protected function _initUrl()
    {
        $baseUrl = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/public/index.php'));
        
        $zcf = Zend_Controller_Front::getInstance();
        
        $zcf->setBaseUrl($baseUrl);
        
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        $url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $baseUrl;
        
        Zend_Registry::set('siteUrl', $url);        
    }
    
    /**
     * We must initialize and setup the session storage for the applications
     * authentication and authorization modules.
     *
     */
    public function _initAuth()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Ot_Auth_Storage_Session(Zend_Registry::get('siteUrl') . 'auth')); 
    }    
        
    protected function _initCache()
    {
        $frontendOptions = array(
            'lifetime'                => 21600, // cache lifetime of 6 hours
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/../cache',
        );
        
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        $cache->setOption('caching', false);
        
        Zend_Registry::set('cache', $cache);        
    }
    
    protected function _initConfig()
    {
        $cache = Zend_Registry::get('cache');
        
        if (!$config = $cache->load('configObject')) {
            
            $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/config.xml', 'production', true);
            
            if (is_file(APPLICATION_PATH . '/../overrides/config/config.xml')) {
                
                $configOverride = new Zend_Config_Xml(APPLICATION_PATH . '/../overrides/config/config.xml', 'production');
                
                if ($configOverride->user instanceof Zend_Config) {
                    foreach ($configOverride->user as $key => $value) {
                        if (isset($config->user->{$key})) {
                            $config->user->{$key}->val = $value->val;
                        }
                    }
                }
            }
           
            $cache->save($config, 'configObject');
        }
        
        Zend_Registry::set('config', $config);

        return $config;       
    }
    
    
    function _initDB()
    {
        $config = Zend_Registry::get('config');
        
        if ($config->app->keymanager) {
            require_once $_SERVER['KEY_MANAGER2_PATH'];
            $km = new KeyManager;
            
            $key = $km->getKey($config->app->keymanager);
            
            $dbConfig = array(
                'adapter'  => 'PDO_MYSQL',
                'username' => $key->username,
                'password' => $key->password,
                'host'     => $key->host,
                'port'     => $key->port,
                'dbname'   => $key->dbname
                );  

            $db = Zend_Db::factory($dbConfig['adapter'], $dbConfig);
            Zend_Db_Table::setDefaultAdapter($db);
            Zend_Registry::set('dbAdapter', $db);                
        }
    }    
    
    public function _initLog()
    {
        $tbl = 'tbl_ot_log';
        
        $config = Zend_Registry::get('config');
        
        if (isset($config->app->tablePrefix) && !empty($config->app->tablePrefix)) {
            $tbl = $config->app->tablePrefix . $tbl;
        }
        
        // Setup logger
        $adapter = Zend_Registry::get('dbAdapter');
        
        $writer = new Zend_Log_Writer_Db($adapter, $tbl);

        $logger = new Zend_Log($writer);

        $logger->addPriority('LOGIN', 8);

        $logger->setEventItem('sid', session_id());
        $logger->setEventItem('timestamp', time());
        $logger->setEventItem('request', str_replace(Zend_Controller_Front::getInstance()->getBaseUrl(), '', $_SERVER['REQUEST_URI']));

        $auth = Zend_Auth::getInstance();

        if (!is_null($auth->getIdentity())) {
            $logger->setEventItem('accountId', $auth->getIdentity()->accountId);
            $logger->setEventItem('role', $auth->getIdentity()->role);
        }

        Zend_Registry::set('logger', $logger);
    }


    public function _initView()
    {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();
        
        $config = Zend_Registry::get('config');
        $view->config = $config;
                            
        $theme = ($config->user->customTheme->val != '') ? $config->user->customTheme->val : 'default';
        
        $appBasePath = APPLICATION_PATH . "/../public/themes";
        $otBasePath = APPLICATION_PATH . "/../public/ot/themes";
        
        if (!is_dir($appBasePath . "/" . $theme)) {
            if (!is_dir($otBasePath . '/' . $theme)) {
                $theme = 'default';
            }
            
            $themePath = $otBasePath . '/' . $theme;
            
        } else {
            $themePath = $appBasePath . '/' . $theme;
        }
        
        $layout->setLayoutPath($themePath . '/views/layouts');
              
        $view->applicationThemePath = str_replace(APPLICATION_PATH . "/../public/", "", $themePath);
        
        $view->addScriptPath(array(
                                $themePath . '/views/scripts/',
                                APPLICATION_PATH . '/views/scripts/',
                                OT_APPLICATION_PATH . '/views/scripts/'
                            ))
             ->addHelperPath(array(
                                $themePath . '/views/helpers/',
                                APPLICATION_PATH . '/views/helpers/',
                                OT_APPLICATION_PATH . '/views/helpers/'
                            ));
    }
    
    protected function _initActionHelpers()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('Ot_Action_Helper');
        Zend_Controller_Action_HelperBroker::addPrefix('Internal_Action_Helper');
    }
}