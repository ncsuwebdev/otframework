<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/DebugController.php';

class DebugControllerTest extends ControllerTestCase
{
    public $debugModeCookieName;
    
    public function setUp()
    {
        $this->debugModeCookieName = $this->getDefaultProperties('Ot_DebugController', '_debugModeCookieName');
        parent::setUp();
    }
    
    public function testIndexWhenDebugModeIsOff()
    {
        $this->login();
        $this->dispatch('/ot/debug/index');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('debug');
        $this->assertAction('index');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testToggleNoStatusGivesException()
    {
        $this->login();
        $this->dispatch('/ot/debug/toggle');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/debug');
        $this->assertModule('ot');
        $this->assertController('debug');
        $this->assertAction('toggle');
    }
    
    public function testToggleOn()
    {
        $this->markTestIncomplete('can\'t do stuff with $_COOKIE through CLI');
        $this->login();
        $this->dispatch('/ot/debug/toggle?status=on');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/debug');
        $this->assertModule('ot');
        $this->assertController('debug');
        $this->assertAction('toggle');
        var_dump($_COOKIE);
        $this->assertEquals(isset($_COOKIE[$this->debugModeCookieName]), true);
        $this->assertEquals($_COOKIE[$this->debugModeCookieName], 1);
    }
    
    /**
     * @depends testToggleOn
     **/
    public function testIndexActionWhenDebugIsOn()
    {
        $this->login();
        $this->dispatch('/ot/debug/index');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('debug');
        $this->assertAction('index');
        
        $this->assertQueryCount('#debugHeader', 1);
    }
    
    public function testToggleOff()
    {
        $this->markTestIncomplete('can\'t do stuff with $_COOKIE through CLI');
        $this->login();
        $this->dispatch('/ot/debug/toggle?status=off');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/debug');
        $this->assertModule('ot');
        $this->assertController('debug');
        $this->assertAction('toggle');
        
        $this->assertEquals(isset($_COOKIE['debugMode']), false);
    }
    
    /**
     * @depends testToggleOff
     **/
    public function testIndexActionWhenDebugIsOff()
    {
        $this->markTestIncomplete('can\'t do stuff with $_COOKIE through CLI');
        $this->login();
        $this->dispatch('/ot/debug/index');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('debug');
        $this->assertAction('index');
        
        $this->assertQueryContentContains('.debugModeOff', 'This application is not in debug mode.');
    }
    
    
}