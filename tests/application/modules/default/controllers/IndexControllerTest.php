<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/default/controllers/IndexController.php';

class DefaultIndexControllerTest extends ControllerTestCase
{
    public function testDefaultAction()
    {
        $this->dispatch('/');
        $this->assertModule('default');
        $this->assertController('index');
        $this->assertAction('index');
    }

}