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
	
	public function wrongDataProvider()
	{
		return array(
			array('', ''),
			array('foobar', ''),
			array('', 'foobar'),
			array('foobar', 'foobar'),
			array('admin', ''),
			array(str_repeat('a', 10000), ''),
			array('asdf', chr(254))
		);
	}
	
	/**
	 * @dataProvider wrongDataProvider
	 */
	public function testLoginFailsWhenGivenInvalidData($username, $password)
	{
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
	
	/**
	 * @dataProvider wrongDataProvider
	 */
	public function testLoginGivesErrorMessageWithMissingUsername($username, $password)
	{
		$this->request
			->setMethod('POST')
			->setPost(array(
				'password'=> $password
			));
		$this->dispatch('ot/login');
		
		$this->assertNotRedirect();
		$this->assertQuery('.errors');
		
	}
	
	/**
	 * @dataProvider wrongDataProvider
	 */
	public function testLoginGivesErrorMessageWithMissingPassword($username, $password)
	{
		$this->request
			->setMethod('POST')
			->setPost(array(
				'username' => $username
			));
		$this->dispatch('ot/login');
		
		$this->assertNotRedirect();
		$this->assertQuery('.errors');
	}
	
	public function testLoginGivesErrorMessagesWithMissingUsernameAndPassword() {
		$this->request
			->setMethod('POST')
			->setPost(array('a'=>''));
		$this->dispatch('ot/login');
		
		$this->assertNotRedirect();
		$this->assertQueryCount('.errors', 2);
	}
	
	public function testLoginIndexRedirectsToRootWhenLoggedIn()
	{
		$this->login();
    	$this->dispatch('ot/login');
    	$this->assertRedirectTo('/');
	}
    
	/**
	 * 
	 */
	public function testForgotActionInvalidUsername()
	{
		//$this->markTestSkipped();
		$this->request
			->setMethod('POST')
			->setPost(array(
				'username' => 'someUserThatDoesntExist'
			));
		$this->dispatch('ot/login/forgot/realm/local');
		$this->assertQuery('#systemMessages');
	}
	
	public function testForgotAction()
	{
		$this->dispatch('ot/login/forgot/realm/local');
		$this->assertQueryCount('form#forgotPassword input', 3);
	}
	
	public function testForgotActionValidUsername() {
		$this->markTestSkipped('AHHHH! PHP native functions are breaking!');
		//$this->markTestSkipped('Test skipped to prevent spamming your inbox.');
		
		//define('MCRYPT_RIJNDAEL_128', 'rijndael-128');
		
		$this->request
			->setMethod('POST')
			->setPost(array(
				'username' => 'admin'
			));
		$this->dispatch('ot/login/forgot/realm/local');
		
		$this->assertRedirect();//To('login/index/realm/local');
	}
	
	public function testForgotActionAlreadyLoggedIn()
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