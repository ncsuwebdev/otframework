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
        $password = 'admin';
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