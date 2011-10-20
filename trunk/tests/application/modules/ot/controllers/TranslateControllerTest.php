<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/TranslateController.php';

class TranslateControllerTest extends ControllerTestCase
{
    
    public function setUp()
    {
        parent::setUp();
        $this->setupDatabase('controllers/account/account.xml');
    }
    
    public function testInit()
    {
        $this->markTestIncomplete();
    }
    
    
    
}