<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/CronController.php';

class CronControllerTest extends ControllerTestCase
{
    
    
    public function testIndexAction()
    {
        $this->markTestSkipped();
        $this->dispatch('/ot/login'); //the redir effects the entire ot/ dir, but check ot/account too
        //$this->assertRedirect();
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('index');
    }
    
    public function testToggleAction()
    {
        $this->markTestSkipped();
        $this->dispatch('/ot/login'); //the redir effects the entire ot/ dir, but check ot/account too
        //$this->assertRedirect();
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('index');
    }
    
    public function wrongLoginDataProvider()
    {
        return array(
            array('', ''),
            //array('foobar', ''),
            array('', 'foobar'),
            array('foobar', 'foobar'),
            array('admin', ''),
            array(str_repeat('a', 10000), ''),
            array('foobar', 'foobar'),
            //array('foobar', 'foobar'),
            array('asdf', chr(254)),
            array(' ', ' ')
        );
    }
    
    /**
     * @dataProvider wrongLoginDataProvider
     */
    public function testLoginFailsWhenGivenInvalidData($username, $password)
    {
        $this->markTestSkipped();
        $this->request
             ->setMethod('POST')
             ->setPost(array(
                 'username' => $username,
                 'password' => $password
             ));
        $this->dispatch('/ot/login');
        
        $this->assertNotRedirect();
        $this->assertQuery('#systemMessages');
    }
    
    
    
}