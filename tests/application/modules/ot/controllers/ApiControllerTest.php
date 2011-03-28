<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/ApiController.php';

class ApiControllerTest extends ControllerTestCase
{
    
	public function testIndexAction()
	{
		$this->dispatch('/ot/api');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('index');
    	
    	
    	$this->markTestIncomplete();
	}
	
	public function testSampleAction()
	{
		$this->dispatch('/ot/api/sample');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('sample');
	}
	
	public function testSoapAction()
	{
		// @todo - figure out ob_get_clean() error
		$this->markTestSkipped('Always gives ob_get_clean() error');
		
		// CLI doesn't define some global variables, which ends up giving errors on library/Oauth/Request.php
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$this->dispatch('/ot/api/soap');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('soap');
    	
    	$this->markTestIncomplete();
	}
	
	public function testXmlAction()
	{
		// CLI doesn't define some global variables, which ends up giving errors on library/Oauth/Request.php
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$this->dispatch('/ot/api/xml');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('xml');
    	
    	
    	$this->markTestIncomplete();
	}
	
	public function testXmlActionEmptyFails()
	{
		// CLI doesn't define some global variables, which ends up giving errors on library/Oauth/Request.php
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$this->dispatch('/ot/api/xml');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('xml');
    	
    	$this->assertQueryContentContains('message', 'No Method Specified.');
    	$this->assertQueryContentContains('status', 'failed');
    	
	}
	
	public function testJsonAction()
	{
		// CLI doesn't define some global variables, which ends up giving errors on library/Oauth/Request.php
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$this->dispatch('/ot/api/json');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('json');
    	
    	$this->markTestIncomplete();
	}
	
	public function testJsonActionXssInJsoncallback()
	{
		// CLI doesn't define some global variables, which ends up giving errors on library/Oauth/Request.php
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$this->dispatch('/ot/api/json?method=describe&jsoncallback=<script>alert(1);</script>');
		
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('json');
    	
    	$this->assertQueryCount('script', 0, 'XSS in jsoncallback param');
	}
    
    public function testJsonActionEmptyFails()
    {
    	$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
    	$this->login();
    	$this->getRequest()
    	    ->setHeader('X-Requested-With', 'XMLHttpRequest')
    	    ->setQuery('format', 'json');
    	
        $this->dispatch('/ot/api/json/');
        
        $this->assertModule('ot');
    	$this->assertController('api');
    	$this->assertAction('json');
        
        $content = json_decode($this->response->outputBody(), true);
        
        $matchAgainst = array(
			'rest' => array(
				'response' => array(
        			'message' => 'No Method Specified.',
        		),
        		'status' => 'failed'
			)
        );
        $this->assertEquals($matchAgainst, $content);
        
    }
    
    public function testJsonErrorsOutputInJson()
    {
    	$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->getRequest()
    	    ->setHeader('X-Requested-With', 'XMLHttpRequest')
    	    ->setQuery('format', 'json');
    	$this->dispatch('/ot/api/json/?method=getMyAccount');
    	var_dump(json_decode($this->response->outputBody()));
    	var_dump(json_last_error());exit;
    	
    	
    }
    
    public function testJsonAndXmlOutputEquivalentData()
    {
    	
    	$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
    	$this->dispatch('/ot/api/json/?method=describe');
    	$jsonResponseObject = $this->response->outputBody();
    	
    	$this->_response = null;
    	// clear the response, so the xml that we grab next doesn't have json before it.
    	
    	$this->dispatch('/ot/api/xml/?method=describe');
    	
    	// this might make this test useless....:
    	$xmlResponseObject = Zend_Json::fromXml($this->response->outputBody());
    	
    	$this->assertEquals($jsonResponseObject, $xmlResponseObject);
    }
    
}