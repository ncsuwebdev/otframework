<?php
class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    public $application;

    public function setUp()
    {
        $this->application = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function tearDown()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();

        $this->request->setPost(array());
        $this->request->setQuery(array());
    }
    
    
    /**
     * link to an xml file that will store a fake database that can easily
     * and quickly have its contents refreshed for retesting
     * the xml file must exist in tests/_files/
     */
    public function setupDatabase($xmlPath = 'dbtest.xml')
    {
    	// @todo: fix database integration from within the ControllerTestCase
    	// currently doesn't work, so return early
    	return;
    	
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
		$databaseFixture = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(dirname(__FILE__) . '/../_files/' . $xmlPath);
		$databaseTester->setupDatabase($databaseFixture);
    }

    public function appBootstrap()
    {
        $this->application->bootstrap();
    }
    
    public function dispatch($url = null)
    {
        // redirector should not exit
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);

        // json helper should not exit
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;

        $request = $this->getRequest();
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
     * logs you into admin account so that you won't get redirected to login page all the time
     * 
     **/
	public function login()
	{
		$username = 'admin';
        $password = 'admin';//is putting a user's u+p in plaintext here ok to do?
        $authAdapter = new Ot_Auth_Adapter;
        $adapter     = $authAdapter->find('local');
        $className   = (string)$adapter->class;
        // Set up the authentication adapter
        $authAdapter = new $className($username, $password);
        $auth = Zend_Auth::getInstance();
        // Attempt authentication, saving the result
        $result = $auth->authenticate($authAdapter);
	}
	
}