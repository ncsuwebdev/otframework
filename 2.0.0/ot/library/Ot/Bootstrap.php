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
class Ot_Bootstrap
{
	/**
	 * Paths include for this version of the framework
	 */
	protected $_includePaths = array();

	/**
	 * The current instance of the bootstrap
	 *
	 * @var Ot_Bootstrap
	 */
	protected static $_instance = null;

	/**
	 * Base URL of the application
	 *
	 * @var unknown_type
	 */
	protected $_baseUrl = '';

	/**
	 * Database adapter object
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db = null;
	
	/**
	 * Boolean to determine caching or not.
	 *
	 * @var unknown_type
	 */
	protected $_caching = false;
	
	/**
	 * Required zend framework verison 
	 *
	 */
	const ZF_VERSION = '1.7.5';

	/**
	 * Hide constructor as this is a singleton method
	 */
	private function __construct()
	{}

    /**
     * Privatized clone function for singleton pattern.
     */
    private function __clone() {}

    /**
     * Singleton pattern instantiation
     *
     * @param String $configSection
     * @return BaseApp_Loader
     */
    public function getInstance($includePaths = array())
    {
        if (null === self::$_instance) {
            $instance = new self();

            $instance->_includePaths = $includePaths;

			// We want all errors reported
			error_reporting(E_ALL|E_STRICT);

			// Setup the include path to point to the desired directories
			$instance->setupIncludePath();
			
			// Turn on autoload so we don't have to specifically include classes
			require_once 'Zend/Loader.php';
			Zend_Loader::registerAutoload();

			// Checks the version of ZF to make sure it is compatible with this framework version
			$instance->zendFrameworkVersionCheck();
						
			self::$_instance = $instance;
        }

        return self::$_instance;
    }

	/**
	 * Dispatch the bootstrap
	 *
	 * @param array $configFiles
	 * @param array $dbConfig
	 */
	public function dispatch($dbConfig)
	{
		// Define the base URL of the application
		$this->_baseUrl = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/index.php'));

		// Define the base http path to the app
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        $url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $this->_baseUrl;
        Zend_Registry::set('siteUrl', $url);

        // initializes the cache and adds it to the registry
        $this->setupCache();
        
		// Register the config file path
		Zend_Registry::set('configFilePath', './config');

		$config  = $this->setupConfig('./config', 'config');

		// Set the default timezone based on what the
		date_default_timezone_set($config->user->timezone->val);

		// Setup our DB connection.
		$this->setupDatabase($dbConfig);

        // Initialize the Auth module
        $this->setupAuth();

		// We need to initialize the view
		$this->setupView();

		// We initialize the front controller to handle all routing of requests
		// to the controllers
		$this->setupFrontController();
		
		Zend_Controller_Action_HelperBroker::addPrefix('Ot_Action_Helper');
		Zend_Controller_Action_HelperBroker::addPrefix('Internal_Action_Helper');
		
        // Setup standard logging adapters
        $this->setupLog();

    	$front = Zend_Controller_Front::getInstance();
    	    	
        try {
            $front->dispatch();
        } catch (Exception $e) {

        	// We remove the request from the namespace if there is an error
            $req = new Zend_Session_Namespace('request');
            $req->unsetAll();

            throw $e;
        }
	}

    /**
     * We alter PHP's include path for convenience of including packages from
     * our library and our models.
     *
     */
    public function setupIncludePath()
    {
    	$basepath = preg_replace('/\/ot\/library\/.*$/i', '', dirname(__FILE__));

    	$appbasepath = preg_replace('/\/index.php/i', '', $_SERVER['SCRIPT_FILENAME']);

        $path = get_include_path() . PATH_SEPARATOR;

        foreach ($this->_includePaths as $i) {
        	$path .= $i . PATH_SEPARATOR;
        }

        $path .= $basepath . '/ot/library' . PATH_SEPARATOR .
            $basepath . '/ot/application/models/' . PATH_SEPARATOR .
            $appbasepath . '/library' . PATH_SEPARATOR .
            $appbasepath . '/application/models/'
            ;
        
        set_include_path($path);

    }
    
    /**
     * Ensures the version of Zend Framework provided is the one required for this 
     * version of OT Framework.
     *
     */
    public function zendFrameworkVersionCheck()
    {
    	if (Zend_Version::VERSION != self::ZF_VERSION) {
    		throw new Exception('Zend Framework version ' . self::ZF_VERSION . ' required for this instance.  ' . Zend_Version::VERSION . ' provided.');
    	}
    }

    /**
     * Enables various parts of the application to be cached.
     *
     */
    public function enableCaching()
    {
    	$this->_caching = true;
    }
    
    /**
     * We create a new database adapter from config options provided from
     * app.php, which are passed from the constructor.  We also set a dbAdapter
     * variable in the registry for access from other models and classes.
     *
     * @param array $dbConfig = array(
     *     'adapter'  => adapter name,
     *     'username' => db username,
     *     'password' => db password,
     *     'host'     => db hostname,
     *     'port'     => db port number,
     *     'dbname'   => db name
     */
    public function setupDatabase($dbConfig)
    {
    	$this->_db = Zend_Db::factory($dbConfig['adapter'], $dbConfig);
        Zend_Db_Table::setDefaultAdapter($this->_db);
        Zend_Registry::set('dbAdapter', $this->_db);
    }
    
    /**
     * Sets up caching and adds the cache object to the registry
     */
    public function setupCache() 
    {
        $frontendOptions = array(
            'lifetime'                => 7200, // cache lifetime of 2 hours
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => '/tmp',
        );
        
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        $cache->setOption('caching', $this->_caching);
        
        Zend_Registry::set('cache', $cache);
    }

    /**
     * Imports the requested XML config file and registers it by name in the
     * Zend_Registry
     *
     * @param string $path - Path to the XML File
     * @param string $name - Name of the registry variable containing the data
     * @return Zend_Config
     */
    public function setupConfig($path, $name)
    {	
        $cache = Zend_Registry::get('cache');
        
      	if (!$config = $cache->load('configObject')) {
      	    
      	    $config = new Zend_Config_Xml($path . '/config.xml', 'production', true);
      	    
      	    if (is_file($config->app->overridePath . '/config/config.xml')) {
      	    	
      	    	$configOverride = new Zend_Config_Xml($config->app->overridePath . '/config/config.xml', 'production');
	      	    
	      	    foreach ($configOverride->user as $key => $value) {
	      	    	if (isset($config->user->{$key})) {
	      	    		$config->user->{$key}->val = $value->val;
	      	    	}
	      	    }
      	    }
      	   
      	    $cache->save($config, 'configObject');
        }
        
    	Zend_Registry::set($name, $config);

        return $config;
    }

    /**
     * We must initialize and setup the session storage for the applications
     * authentication and authorization modules.
     *
     */
    public function setupAuth()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Ot_Auth_Storage_Session($_SERVER['SERVER_NAME'] . $this->_baseUrl . 'auth')); 
    }

    /**
     * Setup of the standard logger for all apps.
     *
     */
    public function setupLog()
    {
        $tbl = 'tbl_ot_log';
        
        $config = Zend_Registry::get('config');
        
    	if (isset($config->app->tablePrefix) && !empty($config->app->tablePrefix)) {
			$tbl = $config->app->tablePrefix . $tbl;
		}
		
        // Setup logger
        $writer = new Zend_Log_Writer_Db($this->_db, $tbl);

        $logger = new Zend_Log($writer);

        $logger->addPriority('LOGIN', 8);

        $logger->setEventItem('sid', session_id());
        $logger->setEventItem('timestamp', time());
        $logger->setEventItem('request', str_replace($this->_baseUrl, '', $_SERVER['REQUEST_URI']));

        $auth = Zend_Auth::getInstance();

        if (!is_null($auth->getIdentity())) {
            $logger->setEventItem('accountId', $auth->getIdentity()->accountId);
            $logger->setEventItem('role', $auth->getIdentity()->role);
        }

        Zend_Registry::set('logger', $logger);
    }

    /**
     * For HTTP connections, we must setup the V of MVC.  In our case, we are
     * going to be using Zend_Layout as the layout manager, which is integrated
     * with Zend's ViewRenderer.
     *
     */
    public function setupView()
    {
        // Configure Zend_Layout
        $layout = Zend_Layout::startMvc('./application/views/layouts/');
        
        $view = $layout->getView();
        
        $view->config = Zend_Registry::get('config');
        
        $view->addScriptPath(array(
                                './application/views/scripts/',
                                './ot/application/views/scripts/'
                            ))
             ->addHelperPath(array(
                                './application/views/helpers/',
                                './ot/application/views/helpers/'
                            ));
    }

    /**
     * For HTTP Requests, we must setup the Front Controller to manage all
     * requests coming into the application.  The Front Controller is
     * responsible for routing all requests to the appropriate controller
     * and action.
     *
     */
    public function setupFrontController()
    {
        $front = Zend_Controller_Front::getInstance();
        
        $router = new Zend_Controller_Router_Rewrite();
        
        $front->setBaseUrl($this->_baseUrl)
              ->addModuleDirectory('./application/modules')
              ->addModuleDirectory('./ot/application/modules')
              ->setRouter($router)
              ->throwExceptions(false);

        $eHandlerConfig = array(
            'module'     => 'error',
            'controller' => 'error',
            'action'     => 'error',
        );

        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler($eHandlerConfig))
              ->registerPlugin(new Ot_FrontController_Plugin_Language())
              ->registerPlugin(new Ot_FrontController_Plugin_Input())
              ->registerPlugin(new Ot_FrontController_Plugin_Auth())
              ->registerPlugin(new Ot_FrontController_Plugin_Htmlheader())
              ->registerPlugin(new Ot_FrontController_Plugin_Nav())
              ->registerPlugin(new Ot_FrontController_Plugin_MaintenanceMode());
    }
}