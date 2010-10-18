<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/LoginController.php';

class LoginControllerTest extends ControllerTestCase
{
	public function testIndexAction()
	{
		$this->dispatch('/ot/login'); //the redir effects the entire ot/ dir, but check ot/account too
		//$this->assertRedirect();
		$this->assertModule('ot');
    	$this->assertController('login');
    	$this->assertAction('index');
	}
	
	public function testLoginFailsWhenGivenInvalidData()
	{
	
		$_POST['username'] = 'UserThatDoesntExist';
		$_POST['password'] = 'PasswordThatDoesntExist';
	
		/*
		$this->request->setMethod('POST');
		$this->request->setPost(array(
					'username' => 'UserThatDoesntExist',
					'password' => 'AnInvalidPassword'
				));*/
		$this->dispatch('/ot/login');
		$this->assertNotRedirect();
		$this->assertQuery('form .errors');
	}
	
	public function testLoginIndexRedirectsToRootWhenLoggedIn()
	{
		$this->login();
    	$this->dispatch('/ot/login');
    	$this->assertRedirectTo('/');
	}
    
	public function testForgotAction()
	{
		$this->markTestIncomplete();
	}
	
	public function testForgotActionGivesMessageWhenUserDoesntExist()
	{
	//The user account you entered was not found.
		$this->markTestIncomplete();
	}
	
	public function testForgotActionGivesMessageOnValidDataAndRedirectsToLogin()
	{
	//A password reset request was sent to the email address on file.
		$this->markTestIncomplete();
	}
	
	
	public function testPasswordResetAction()
	{
    	$this->markTestIncomplete();
    }
    
    public function testLogoutAction()
    {
    	//$this->assertRedirect();
        $this->markTestIncomplete();
    }
    
    public function testSignupAction()
    {
        $this->markTestIncomplete();
    }
    
}