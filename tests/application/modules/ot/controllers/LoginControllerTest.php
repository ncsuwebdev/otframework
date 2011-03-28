<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/LoginController.php';

class LoginControllerTest extends ControllerTestCase
{
	
	public function setUp()
	{
		parent::setUp();
		$this->setupDatabase();
	}
	
	
	public function testIndexAction()
	{
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
	 * @dataProvider wrongLoginDataProvider
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
	 * @dataProvider wrongLoginDataProvider
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
		$this->markTestSkipped();
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
	
	
	
	
    public function testSignupValid()
    {
    	$this->request
    	     ->setMethod('POST')
    	     ->setPost(
    	     	array(
    	     		'username'     => 'john',
    	     		'password'     => 'admin',
    	     		'passwordConf' => 'admin',
    	     		'firstName'    => 'John',
    	     		'lastName'     => 'Smith',
    	     		'emailAddress' => 'a@a.com',
    	     		'timezone'     => 'America/New_York',
    	     		'realm'        => 'local',
    	     	)
    	     );
    	     $this->dispatch('/ot/login/signup/realm/local');
    	     $this->assertRedirectTo('login/index/realm/local');
    }
	
	/**
	 * @depends testSignupValid
	 */
	public function testForgotActionValidUsername() {
		//$this->markTestSkipped('AHHHH! PHP native functions are breaking!');
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
    
    public function testSignupIndexAction()
    {
    	$this->dispatch('ot/login/signup/realm/local');
    	$this->assertQueryCount('form#account input', 8);
		$this->assertQueryCount('form#account input[type="text"]', 4);
		$this->assertQueryCount('form#account input[type="password"]', 2);
		$this->assertQueryCount('form#account input[type="hidden"]', 1);
		$this->assertQueryCount('form#account select', 1);
		$this->assertQueryCount('form#account button', 1);
		$this->assertQueryCount('.required', 6);
    }
    
    public function wrongSignupDataProvider()
    {
    	return array(
			/*  0 */ array('', '', '', '', '', '', '', '', 'everything blank'),
			/*  1 */ array(' ',     'password', 'password',  'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'short username'),
			/*  2 */ array('a',     'password', 'password',  'first', 'last', "           asdf@a.com\r\n\r\n",         'America/New_York', 'local', 'short username'),
			/*  3 */ array('admin', 'pass',     'pass',      'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'password too short'),
			/*  4 */ array('admin', 'password', 'different', 'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'different confirm password'),
			/*  5 */ array('admin', 'password', 'password',  '',      'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'no first name'),
			/*  6 */ array('admin', 'password', 'password',  'first', '',     'srgraham@ncsu.edu',         'America/New_York', 'local', 'no last name'),
			/*  7 */ array('admin', 'password', 'password',  'first', 'last', '',                          'America/New_York', 'local', 'no email'),
			/*  8 */ array('admin', 'password', 'password',  'first', 'last', 'email',                     'America/New_York', 'local', 'invalid email address'),
			/*  9 */ array('admin', 'password', 'password',  'first', 'last', "srgraham@ncsu.edu\r\n\r\n", 'America/New_York', 'local', 'invalid email'),
			/* 10 */ array('admin', 'password', 'password',  'first', 'last', 'srgraham@ncsu.edu',         '\'"<>aaaa',        'local', 'invalid timezone'),
			/* 11 */ array('admin', 'password', 'password',  'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', '',      'blank realm'),
			/* 12 */ array('admin', 'password', 'password',  'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'hello', 'invalid realm'),
			
			//array('admin', 'password', 'password',  'first', 'last', 'email@address.com',         'America/New_York', 'local', 'message'),
		);
    }
    
    
    /**
	 * @dataProvider wrongSignupDataProvider
	 * @depends testSignupValid
	 */
    public function testSignupInvalidData($username, $password, $cPassword, $fName, $lName, $email, $timezone, $realm, $message)
    {
		$this->request
			->setMethod('POST')
			->setPost(
				array(
					'username'     => $username,
					'password'     => $password,
					'passwordConf' => $cPassword,
					'firstName'    => $fName,
					'lastName'     => $lName,
					'emailAddress' => $email,
					'timezone'     => $timezone, 
					'realm'        => $realm,
				)
		);
			
		$this->dispatch('/ot/login/signup/realm/local');
		
		$this->assertNotRedirect();
		$this->assertQuery('#systemMessages');
		$this->assertQuery('.errors', $message);
    }
    
}