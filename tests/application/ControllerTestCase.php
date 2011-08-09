<?php

class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    public $application;
    public $config;
    public $loggedIn = false;
    protected static $dbTester;

    /**
     * this runs at the start of each controller
     * load the default database setup for each class, then make adjustments based on what needs to be changed
     * */
    
    
    public function setUp()
    {
        $this->application = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
        $this->bootstrap = array($this, 'appBootstrap');
        
        parent::setUp();
        
        // CLI doesn't define some global variables, which ends up giving errors on
        // library/Oauth/Request.php and possibly other places too
        
        $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/../applcation';
        $_SERVER['HTTP_HOST'] = 'localhost';
        
        $this->config = Zend_Registry::get('config');
    }

    public function tearDown()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();

        $this->request->setPost(array());
        $this->request->setQuery(array());
        
        // unset these for optimization (globals get saved which slow down tests, so clear
        // them here to make it run faster for these globals we don't care about)
        /*unset($_SERVER['DOCUMENT_ROOT']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_METHOD']);*/
        if($this->loggedIn) {
            $this->logout();
        }
        
    }
    
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::setupDatabase();
    }
    
    public static function tearDownAfterClass()
    {
        
    }
    
    /**
     * link to an xml file that will store a fake database that can easily
     * and quickly have its contents refreshed for retesting
     * the xml file must exist in tests/_files/
     */
    public static function setupDatabase($xmlPath = 'dbtest.xml')
    {
        
        if (!self::$dbTester) {
            $configFilePath = dirname(__FILE__) . '/../../application/configs';
            $applicationIni = new Zend_Config_Ini($configFilePath . '/application.ini', 'testing');
            
            
            $adapter = $applicationIni->resources->db->adapter;
            $params = array(
                'username' => $applicationIni->resources->db->params->username,
                'password' => $applicationIni->resources->db->params->password,
                'host'     => $applicationIni->resources->db->params->host,
                'port'     => $applicationIni->resources->db->params->port,
                'dbname'   => $applicationIni->resources->db->params->dbname
            );
            
            $db = Zend_Db::factory($adapter, $params);
            $connection = new Zend_Test_PHPUnit_Db_Connection($db, 'mysql');
            $databaseTester = new Zend_Test_PHPUnit_Db_SimpleTester($connection);
            self::$dbTester = $databaseTester;
        } else {
            $databaseTester = self::$dbTester;
        }
        
        $databaseFixture = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(dirname(__FILE__) . '/../_files/' . $xmlPath);
        $databaseTester->setupDatabase($databaseFixture);
    }

    public function appBootstrap()
    {
        $this->application->bootstrap();
    }
    
    public function dispatch($url = null)
    {
        // this is a reminder to me for catching some of the times I make post data but forget to send it
        if(($this->request->getMethod() == 'POST' && !$this->request->getPost())
            || ($this->request->getMethod() != 'POST' && $this->request->getPost())
        ) {
            $this->fail('!!!AHH, post data not set right!!!');
        }
        
        // redirector should not exit
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);

        // json helper should not exit
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;

        $request = $this->getRequest();
        
        // fetch method into $_SERVER because CLI doesn't set it automatically
        $_SERVER['REQUEST_METHOD'] = $request->getMethod();
        if(!$_SERVER['REQUEST_METHOD']) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
        
        if (null !== $url) {
            $request->setRequestUri($url);
        }
        $request->setPathInfo(null);

        $this->getFrontController()
             ->setRequest($request)
             ->setResponse($this->getResponse())
             ->throwExceptions(true)
             ->returnResponse(false);
        $this->getFrontController()->dispatch();
        
        
    }
    
    /**
     * logs you into an account so that you won't get redirected to login page all the time
     * defaults to dbtest.xml's admin account
     **/
    public function login($username = 'admin', $password = 'admin')
    {
        if($this->loggedIn) {
            return;
        }
        $authAdapter = new Ot_Auth_Adapter();
        $adapter     = $authAdapter->find('local');
        $className   = (string)$adapter->class;
        // Set up the authentication adapter
        $authAdapter = new $className($username, $password);
        $auth = Zend_Auth::getInstance();
        // Attempt authentication, saving the result
        $result = $auth->authenticate($authAdapter);
        $this->loggedIn = true;
    }
    
    /**
     * logs you out
     */
    public function logout()
    {
        if(!$this->loggedIn) {
            return;
        }
        //$config = Zend_Registry::get('config');
        $userId = Zend_Auth::getInstance()->getIdentity();
        // Set up the auth adapter
        $authAdapter = new Ot_Auth_Adapter();
        $adapter     = $authAdapter->find('local');
        $className   = (string)$adapter->class;
        $auth        = new $className();
        $auth->autoLogout();
        Zend_Auth::getInstance()->clearIdentity();
        $this->loggedIn = false;
    }
    
    /**
     * convert xml to assoc array
     */
    public function xmlToArray($xmlStr)
    {
        $xmlObj = simplexml_load_string($xmlStr);
        return $this->objectsIntoArray($xmlObj);
        
    }
    
    public function objectsIntoArray($arrObjData) {
        $arrData = array();
   
        // if input is object, convert into array
        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }
       
        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = $this->objectsIntoArray($value); // recursive call
                }
                $arrData[$index] = $value;
            }
        }
        //$arrayRemove = array('@attributes' => '');
        //$arrData = array_diff_assoc($arrData, $arrayRemove);
        if(isset($arrData['@attributes'])) {
            unset($arrData['@attributes']);
        }
        return $arrData;
    }
    
    /**
     * Gets the default properties for a class. This bypasses protected and private protections
     * if $propertyName set, returns the value of the specified property
     * if $propertyName not set, returns all the properties
     * 
     * PHPUnit_Framework_Assert::readAttribute() / $this->assertAttributeEquals() may be better
     */
    public function getDefaultProperties($className, $propertyName)
    {
        $reflection = new ReflectionClass($className);
        $defaults = $reflection->getDefaultProperties();
        if(is_array($defaults)) {
            if($propertyName) {
                if(isset($defaults[$propertyName])) {
                    return $defaults[$propertyName];
                } else {
                    //missing property!!
                    return NULL;
                }
            } else {
                return $defaults;
            }
        }
    }
    
}