<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/LoginController.php';

class LoginControllerTest extends ControllerTestCase
{
    
    public function setUp()
    {
        parent::setUp();
        $this->setupDatabase('controllers/login/login.xml');
        $this->logout();
    }
    
    
    public function testIndexAction()
    {
        $this->dispatch('/ot/login?realm=local'); //the redir effects the entire ot/ dir, but check ot/account too
        //$this->assertRedirect();
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('index');
    }
    
    public function testIndexActionWithValidLoginData()
    {
        
        $postData = array(
            'username' => 'admin',
            'password' => 'admin',
        );
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('/ot/login?realm=local'); //the redir effects the entire ot/ dir, but check ot/account too
        $this->assertRedirectTo('/');
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('index');
    
    }
    
    public function wrongLoginDataProvider()
    {
        return array(
            array('', ''),
            array('', 'foobar'),
            array('foobar', 'foobar'),
            array('admin', ''),
            array(str_repeat('a', 10000), ''),
            array('foobar', 'foobar'),
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
    
    public function testForgotActionRedirectsWhenLoggedIn()
    {
        $this->login();
        $this->dispatch('ot/login/forgot');
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('forgot');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/');
        
    
    }
    
    
    
    public function testSignupValid()
    {
        //$this->markTestSkipped('Known bug with this file, but I don\'t know if that bug is causing the redirect report problem.');
        $this->request
            ->setMethod('POST')
            ->setPost(
                array(
                    'username'     => 'NEWUSERPERSON',
                    'password'     => 'admin',
                    'passwordConf' => 'admin',
                    'firstName'    => 'John',
                    'lastName'     => 'Smith',
                    'emailAddress' => 'srgraham@ncsu.edu',
                    'timezone'     => 'America/New_York',
                    'realm'        => 'local',
                )
        );
        $this->dispatch('/ot/login/signup/realm/local');
        $this->assertRedirectTo('/login/index/realm/local');
    }
    
    /**
     * @depends testSignupValid
     */
    public function testForgotActionValidUsername() {
        $this->markTestSkipped('Test skipped to prevent spamming your inbox.');
        
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
        // @todo - load blank account database table
    //The user account you entered was not found.
        $this->markTestIncomplete();
    }
    
    public function testForgotActionGivesMessageOnValidDataAndRedirectsToLogin()
    {
        
        $this->markTestSkipped('MCRYPT gives error in CLI');
        
        // @todo - add table with admin as only user
        $this->markTestIncomplete('Add table with admin as only user');
        
        //A password reset request was sent to the email address on file.
        
        $this->request
            ->setMethod('POST')
            ->setPost(
                array(
                    'username' => 'admin'
                )
        );
        $this->dispatch('ot/login/forgot?realm=local');
        
        
    }
    
    
    public function testPasswordResetActionIgnoresWhenLoggedIn()
    {
        $this->login();
        $this->dispatch('ot/login/password-reset');
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('password-reset');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testPasswordResetActionGiversExceptionWithoutKey()
    {
        $this->dispatch('ot/login/password-reset/?key=');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testPasswordResetActionGivesExceptionWithInvalidKey()
    {
        $this->markTestSkipped('"Couldn\'t find constant MCRYPT_RIJNDAEL_128" with CLI');
        $this->dispatch('ot/login/password-reset/?key=ahlkdhalksdhlakhdlkha5487o');
    }
    
    
    public function testLogoutAction()
    {
        $this->login();
        $this->dispatch('ot/login/logout');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/');
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('logout');
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
            /*  0 */ array('', '', '', '', '', '', '', 'local', 'everything blank (except realm)'),
            /*  1 */ array(' ',     'password', 'password',  'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'space for username'),
            /*  2 */ array('a',     'password', 'password',  'first', 'last', "           srgraham@ncsu.edu\r\n\r\n",         'America/New_York', 'local', 'short username'),
            /*  3 */ array('admin', 'pass',     'pass',      'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'password too short'),
            /*  4 */ array('admin', 'password', 'different', 'first', 'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'different confirm password'),
            /*  5 */ array('admin', 'password', 'password',  '',      'last', 'srgraham@ncsu.edu',         'America/New_York', 'local', 'no first name'),
            /*  6 */ array('admin', 'password', 'password',  'first', '',     'srgraham@ncsu.edu',         'America/New_York', 'local', 'no last name'),
            /*  7 */ array('admin', 'password', 'password',  'first', 'last', '',                          'America/New_York', 'local', 'no email'),
            /*  8 */ array('admin', 'password', 'password',  'first', 'last', 'email',                     'America/New_York', 'local', 'invalid email address'),
            /*  9 */ array('admin', 'password', 'password',  'first', 'last', 'srgraham@ncsu.edu',         '\'"<>aaaa',        'local', 'invalid timezone'),
            
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
                )
        );
            
        $this->dispatch('/ot/login/signup/?realm=' . $realm);
        
        $this->assertNotRedirect();
        $this->assertQuery('#systemMessages');
        $this->assertQuery('.errors', $message);
    }
    
    /**
     * @expectedException Ot_Exception
     */
    public function testSignupWithoutRealm()
    {
        $postData = array(
            'username'     => 'person',
            'password'     => 'admin',
            'passwordConf' => 'admin',
            'firstName'    => 'first',
            'lastName'     => 'last',
            'emailAddress' => 'srgraham@ncsu.edu',
            'timezone'     => 'America/New_York',
        );
        
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('/ot/login/signup');
    }
    
/**
     * @expectedException Ot_Exception
     */
    public function testSignupWithInvalidRealm()
    {
        $postData = array(
            'username'     => 'person',
            'password'     => 'admin',
            'passwordConf' => 'admin',
            'firstName'    => 'first',
            'lastName'     => 'last',
            'emailAddress' => 'srgraham@ncsu.edu',
            'timezone'     => 'America/New_York',
        );
        
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('/ot/login/signup?realm=akhslghasid');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testSignupActionWithoutRealmInGet()
    {
        $this->dispatch('ot/login/signup');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('signup');
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testSignupActionWithUnknownRealmInGet()
    {
        $this->dispatch('ot/login/signup?realm=GEORGE');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('signup');
    }
    
    public function testSignupActionWithUsernameTakenGivesError()
    {
        // @todo: load an xml database that contains admin as a user, so that it will be a duplicate username for this test
        $username  = 'admin';
        $password  = 'admin1';
        $cPassword = 'admin1';
        $fName     = 'Admin';
        $lName     = 'McAdmin';
        $email     = 'srgraham@ncsu.edu';
        $timezone  = 'America/New_York';
        $realm     = 'local';
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
        
        $this->dispatch('ot/login/signup');
        $this->assertQuery('#systemMessages');
    }
    
    public function testSignupActionValid()
    {
        $this->markTestSkipped('Skipped so it doesn\'t spam my email');
        $username  = 'admin123456';
        $password  = 'admin1';
        $cPassword = 'admin1';
        $fName     = 'Admin';
        $lName     = 'McAdmin';
        $email     = 'srgraham@ncsu.edu';
        $timezone  = 'America/New_York';
        $realm     = 'local';
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
        
        $this->dispatch('ot/login/signup');
    }
    
    
    
    
    
    
}