<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/IndexController.php';

class OtIndexControllerTest extends ControllerTestCase
{
    
    public function testIndexAction()
    {
        
        
        $this->login();
        
        $this->dispatch('ot/index/index');
        
        $this->assertModule('ot');
        $this->assertController('index');
        $this->assertAction('index');
        
        $this->assertQuery('table.list tr');
        
        $this->markTestIncomplete();
    }

    
}