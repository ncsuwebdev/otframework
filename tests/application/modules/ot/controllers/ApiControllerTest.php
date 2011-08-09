<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/ApiController.php';

class ApiControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logout();
    }
    
    public function testIndexAction()
    {
        $this->dispatch('/ot/api');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('index');
        
        // nothing really to check here
    }
    
    public function testSampleAction()
    {
        $this->dispatch('/ot/api/sample');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('sample');
        
        // nothing more to check here
    }
    
    public function testSoapAction()
    {
        // @todo - figure out ob_get_clean() error
        // CLI + Zend Framework + Soap always gives ob_get_clean() error, so ignore this error
        @$this->dispatch('/ot/api/soap');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('soap');
        
        $this->markTestIncomplete();
    }
    
    public function testXmlAction()
    {
        $this->dispatch('/ot/api/xml');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('xml');
        
        
        $this->markTestIncomplete();
    }
    
    public function testXmlActionEmptyFails()
    {
        $this->dispatch('/ot/api/xml');
        // @FIXME: commented out this assert because phpunit can't capture the header
        //$this->assertResponseCode(400);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('xml');
        
        $this->assertQueryContentContains('message', 'No Method Specified.');
        $this->assertQueryContentContains('status', 'failed');
    }
    
    public function testXmlGivesErrorOnInvalidData()
    {
        $this->dispatch('/ot/api/xml?method=GEORGE');
        // @FIXME: commented out this assert because phpunit can't capture the header
        //$this->assertResponseCode(400);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('xml');
        $this->assertQueryContentContains('message', "Unknown Method 'GEORGE'.");
        $this->assertQueryContentContains('status', 'failed');
    }
    
    public function testXmlGivesErrorWithoutPermission()
    {
        $this->login();
        $this->dispatch('/ot/api/xml?method=getMyAccount');
        // @FIXME: commented out this assert because phpunit can't capture the header
        //$this->assertResponseCode(401);
        //$this->assertHeader('HTTP/1.1 401 Unauthorized');
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('xml');
        $this->assertQueryContentContains('message', 'You do not have the proper signed credentials to remotely access this method.');
        $this->assertQueryContentContains('status', 'failed');
    }
    
    public function testJsonAction()
    {
        $this->dispatch('/ot/api/json');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('json');
        
        $this->markTestIncomplete();
    }
    
    public function testJsonActionXssInJsoncallback()
    {
        
        $this->dispatch('/ot/api/json?method=describe&jsoncallback=<script>alert(1);</script>');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('json');
        
        $this->assertQueryCount('script', 0, 'XSS in jsoncallback param');
    }
    
    public function testJsonActionEmptyFails()
    {
        $this->getRequest()
            ->setHeader('X-Requested-With', 'XMLHttpRequest')
            ->setQuery('format', 'json');
        
        $this->dispatch('/ot/api/json/');
        
        // @FIXME: commented out this assert because phpunit can't capture the header
        //$this->assertResponseCode(400);
        $this->assertNotRedirect();
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
    
    public function testJsonGivesErrorWithoutPermission()
    {
        $this->login();
        $this->dispatch('/ot/api/json?method=getMyAccount');
        // @FIXME: commented out this assert because phpunit can't capture the header
        //$this->assertResponseCode(401);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('json');
        $content = json_decode($this->getResponse()->outputBody(), true);
        $matchAgainst = array(
            'error' => 'You do not have the proper signed credentials to remotely access this method.',
            'success' => array(),
        );
        $this->assertEquals($matchAgainst, $content);
    }
    
    public function testJsonGivesErrorOnInvalidData()
    {
        $this->dispatch('/ot/api/json?method=GEORGE');
        
        // @FIXME: commented out this assert because phpunit can't capture the header
        //$this->assertResponseCode(400);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('json');
        
        // honestly, I don't like the format output of this, but I don't think we can change it (it's from the Zend library)
        $content = json_decode($this->getResponse()->outputBody(), true);
        $matchAgainst = array(
            'GEORGE' => array(
                'response' => array(
                    'message' => "Unknown Method 'GEORGE'.",
                ),
                'status' => 'failed',
            ),
        );
        $this->assertEquals($matchAgainst, $content);
    }
    
    
    public function testJsonErrorsOutputInJsonFormat()
    {
        //$this->markTestSkipped('phpunit bug causes failure due to header errors when testing this');
        $this->getRequest()
            ->setHeader('X-Requested-With', 'XMLHttpRequest')
            ->setQuery('format', 'json');
        $this->dispatch('/ot/api/json/?method=getMyAccount');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('json');
        
        $matchAgainst = array(
            'success' => array(),
            'error' => 'You do not have the proper signed credentials to remotely access this method.',
        );
        
        $this->assertEquals(json_decode($this->getResponse()->outputBody(), true), $matchAgainst);
        
    }
    
    public function testJsonAndXmlOutputEquivalentData()
    {
        
        $this->dispatch('/ot/api/json/?method=describe');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('json');
        
        $jsonResponseObject = $this->response->outputBody();
        
        $this->_response = null;
        // clear the response, so the xml that we grab next doesn't have json before it.
        
        $this->dispatch('/ot/api/xml/?method=describe');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('api');
        $this->assertAction('xml');
        
        // this might make this test useless....:
        $xmlResponseObject = Zend_Json::fromXml($this->response->outputBody());
        
        $this->assertEquals($jsonResponseObject, $xmlResponseObject);
    }
    
}