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
	 * Libraries to include for this version of the framework
	 */
	protected $_libraries = array();
	
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
    public function getInstance() 
    {
        if (null === self::$_instance) {
            $instance = new self();
                           
			$instance->_libraries['ZF']     = $_SERVER['SHARED_LIB_PATH'] . '/Zend/Framework/1.5/';
			$instance->_libraries['Smarty'] = $_SERVER['SHARED_LIB_PATH'] . '/Smarty/2.6.18/';
			
			// We want all errors reported
			error_reporting(E_ALL|E_STRICT);
			
			// Setup the include path to point to the desired directories
			$instance->setupIncludePath();
			
			// Turn on autoload so we don't have to specifically include classes
			require_once 'Zend/Loader.php';
			Zend_Loader::registerAutoload(); 
			
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
	public function dispatch($configFiles, $dbConfig)
	{
		// Define the base URL of the application
		$this->_baseUrl = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/index.php'));
		Zend_Registry::set('sitePrefix', $this->_baseUrl);
	    
		// Define the base http path to the app
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        $url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $this->_baseUrl;		
        Zend_Registry::set('siteUrl', $url);

		// Register the config file variables so other parts of the app can access them
		Zend_Registry::set('configFiles', $configFiles);
		
		// This config file contains user editable options
		if (!isset($configFiles['user'])) {
			throw new Exception('User config file not set.  Cannot configure application.');
		}
		$userConfig = $this->setupConfig($configFiles['user'], 'userConfig');
		
		// This config file contains application options such as adapters
	    if (!isset($configFiles['app'])) {
            throw new Exception('Application config file not set.  Cannot configure application.');
        }
		$appConfig  = $this->setupConfig($configFiles['app'], 'appConfig');
		
		// Set the default timezone based on what the 
		date_default_timezone_set($userConfig->timezone->value);
		
		// Setup our DB connection.
		$this->setupDatabase($dbConfig);	
					
        // Initialize the Auth module
        $this->setupAuth();
        		
		// This file contains ACL configuration options that are editable
		// by the end user
		if (!isset($configFiles['acl'])) {
            throw new Exception('ACL config file not set.  Cannot configure application.');
        }
		$this->setupConfig($configFiles['acl'], 'aclConfig');
		
		// This file contains the navigation structure
	    if (!isset($configFiles['nav'])) {
                throw new Exception('Navigation config file not set.  Cannot configure application.');
            }
		$this->setupConfig($configFiles['nav'], 'navConfig');
            
		// We need to initialize the view
		$this->setupView();
		
		// We initialize the front controller to handle all routing of requests
		// to the controllers
		$this->setupFrontController();
		
        // Setup standard logging adapters
        $this->setupLog();  	

    	$front = Zend_Controller_Front::getInstance();
    	
        try {
            $front->dispatch();
        } catch (Exception $e) {
        	
        	// We remove the request from the namespace if there is an error
            $req = new Zend_Session_Namespace('request');
            $req->uri = '';
            
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
	
        foreach ($this->_libraries as $l) {
        	$path .= $l . PATH_SEPARATOR;
        }
         
        $path .= $basepath . '/ot/library' . PATH_SEPARATOR . 
            $basepath . '/ot/application/models/' . PATH_SEPARATOR . 
            $appbasepath . '/library' . PATH_SEPARATOR . 
            $appbasepath . '/application/models/'
            ;
            
        set_include_path($path);

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
     * Imports the requested XML config file and registers it by name in the
     * Zend_Registry
     *
     * @param string $path - Path to the XML File
     * @param string $branch - Section of the XML file to read
     * @param string $name   - Name of the registry variable containing the data
     * @return Zend_Config
     */
    public function setupConfig($path, $name)
    {
    	$config = new Zend_Config_Xml($path, 'production');
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

        $authz = Ot_Authz::getInstance();
        $authz->setStorage(new Ot_Auth_Storage_Session($_SERVER['SERVER_NAME'] . $this->_baseUrl . 'authz'));
    }
    
    /**
     * Setup of the standard logger for all apps.
     *
     */
    public function setupLog()
    {
        
        // Setup logger
        $writer = new Zend_Log_Writer_Db($this->_db, 'tbl_ot_log');

        $logger = new Zend_Log($writer);
        
        $logger->addPriority('LOGIN', 8);
        
        $logger->setEventItem('sid', session_id());
        $logger->setEventItem('timestamp', time());
        $logger->setEventItem('request', str_replace($this->_baseUrl, '', $_SERVER['REQUEST_URI']));
        
        $auth = Zend_Auth::getInstance();
        
        if (!is_null($auth->getIdentity())) {
            $logger->setEventItem('userId', $auth->getIdentity());
            $logger->setEventItem('role', Ot_Authz::getInstance()->getRole());
        }
        
        Zend_Registry::set('logger', $logger);    	
    }
    
    /**
     * For HTTP connections, we must setup the V of MVC.  In our case, we are
     * going to be using Zend_Layout as the layout manager, which is integrated
     * with Zend's ViewRenderer.  To render all the HTML, we will be using 
     * Smarty as our templating engine.  This function sets up Zend_Layout and
     * ViewRenderer to pass data through Smarty.
     *
     */
    public function setupView()
    {
        // Create a new View object
        $view = new Ot_View_Smarty();
        $view->sitePrefix = $this->_baseUrl; 
        
        $view->addScriptPath('./application/views/scripts/');   
        $view->addScriptPath('./ot/application/views/scripts/');
        
        // Configure the View Renderer
        $vr = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $vr->setView($view);
        $vr->setViewBasePathSpec('./application/views')
           ->setViewScriptPathSpec(':module/:controller/:action.:suffix')
           ->setViewScriptPathNoControllerSpec(':action.:suffix')
           ->setViewSuffix('tpl');

        
        // Configure Zend_Layout
        $viewOptions = array(
                        'layout'     => 'site',
                        'layoutPath' => './application/views/layouts'
                       );
        
        $layout = Zend_Layout::startMvc($viewOptions);
        $layout->setInflectorTarget(':script.:suffix');
        
        $layout->setViewSuffix('tpl');
        $layout->setView($view);
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
        $front->setBaseUrl($this->_baseUrl)
              ->addModuleDirectory('./application/modules')
              ->addModuleDirectory('./ot/application/modules')
              ->setRouter(new Zend_Controller_Router_Rewrite());

        $eHandlerConfig = array(
            'module'     => 'error',
            'controller' => 'error',
            'action'     => 'error',
        );
        
        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler($eHandlerConfig))
              ->registerPlugin(new Ot_FrontController_Plugin_Auth())
              ->registerPlugin(new Ot_FrontController_Plugin_Htmlheader())
              ->registerPlugin(new Ot_FrontController_Plugin_TextSubstitution())    
              ->registerPlugin(new Ot_FrontController_Plugin_Nav())
              ->registerPlugin(new Ot_FrontController_Plugin_MaintenanceMode());
    }
}