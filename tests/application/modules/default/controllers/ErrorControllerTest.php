<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/default/controllers/ErrorController.php';

class ErrorControllerTest extends ControllerTestCase
{
    public function testErrorActionWithTrackbackOn()
    {
        $config = Zend_Registry::get('config');
        $config->user->showTrackbackOnErrors->val = "1";
        Zend_Registry::set('config', $config);
        $config = Zend_Registry::get('config');
        
        $this->login();
        $this->getFrontController()->throwExceptions(false);
        $this->setExpectedException('Ot_Exception_Data');
        try {
            $this->dispatch('/ot/account?accountId=15150154');
        } catch(Exception $e) {
            $this->assertNotRedirect();
            var_dump(88, $this->getResponse());exit;
            
            $this->assertQueryContentContains('div#content div.ui-state-error', 'User Account Not Found');
            $this->assertQueryCount('div#trackback table.list', 1);
            return;
        }
        $this->fail('Expected an exception.');
    }
    
    public function testErrorActionWithTrackbackOff()
    {
        $config = Zend_Registry::get('config');
        $config->user->showTrackbackOnErrors->val = "0";
        Zend_Registry::set('config', $config);
        $config = Zend_Registry::get('config');
        
        $this->login();
        $this->dispatch('/ot/account?accountId=15150154');
        $this->assertNotRedirect();
        $this->assertQueryContentContains('div#content div.ui-state-error', 'User Account Not Found');
        $this->assertQueryCount('div#trackback table.list', 0);
    
    }
    
    /**
     * @expectedException Zend_Controller_Dispatcher_Exception
     */
    public function testNotFoundSets404Header() {
        $this->dispatch('/aksdhglah');
        $this->assertResponseCode(404);
    }
}