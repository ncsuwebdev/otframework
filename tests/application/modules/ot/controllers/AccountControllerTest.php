<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/AccountController.php';

class AccountControllerTest extends ControllerTestCase
{
	public function testInit()
	{
		$this->markTestIncomplete();
	}
    
	public function testLoginPageRedirectWhenNotLoggedIn()
	{
		$this->dispatch('/ot/account'); //the redir effects the entire ot/ dir, but check ot/account too
		//$this->assertRedirect();
		$this->assertModule('ot');
    	$this->assertController('login');
    	$this->assertAction('index');
	}
	
	public function testIndexAction()
	{
    	$this->login();
    	$this->dispatch('/ot/account');
    	
    	$this->assertNotRedirect();
    	
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('index');
        
        $this->assertQueryCount('table.form tr', 7, 'Missing table data.');

    }
    
    public function testAllAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testAddAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testEditAction()
    {
    	$this->login();
    	$this->dispatch('/ot/account/edit/accountId/31');
    	$this->assertQueryCount('form#account input[type="text"]', 3);
    	$this->assertQuery('form#account select');
    	$this->assertQuery('form#account input[type="hidden"]');
    	
    	
    	
        $this->markTestIncomplete();
    }
    
    public function testDeleteAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testRevokeConnectionAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testChangePasswordAction()
    {
        $this->markTestIncomplete();
    }

    public function testChangeUserRoleAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testEditAllAccountsAction()
    {
        $this->markTestIncomplete();
    }
}