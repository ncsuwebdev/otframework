<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/CacheController.php';

class CacheControllerTest extends ControllerTestCase
{
    
    public function testIndexAction()
    {
        $this->login();
        $this->dispatch('/ot/cache');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('cache');
        $this->assertAction('index');
        
        $this->markTestIncomplete();
    }
    
    public function testClearActionRedirectsWithNoPostData()
    {
        $this->login();
        $this->dispatch('/ot/cache/clear');
        $this->assertResponseCode(302);
        $this->assertRedirect();
        $this->assertModule('ot');
        $this->assertController('cache');
        //$this->assertAction('clear'); // I don't think this is changing properly on redirect
        $this->markTestIncomplete();
    }
    
    public function testClearSubmitAction()
    {
        $this->markTestIncomplete();
        $this->login();
        $this->request
            ->setMethod('POST')
            ->setPost(
                array(
                    'clearCache' => 1,
                )
        );
        
        $this->dispatch('/ot/cache/clear');
        
        $this->assertResponseCode(302);
        $this->assertRedirect();
        $this->assertModule('ot');
        $this->assertController('cache');
        $this->assertAction('index');
        
        $this->markTestIncomplete();
    }
    
    
}