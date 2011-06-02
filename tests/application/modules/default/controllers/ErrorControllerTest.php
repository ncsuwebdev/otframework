<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/default/controllers/ErrorController.php';

class ErrorControllerTest extends ControllerTestCase
{
    public function testErrorAction()
    {
        $this->markTestIncomplete();
    }
	
    /**
     * @expectedException Zend_Controller_Dispatcher_Exception
     */
    public function testNotFoundSets404Header() {
    	$this->dispatch('/aksdhglah');
    	$this->assertResponseCode(404);
    }
}