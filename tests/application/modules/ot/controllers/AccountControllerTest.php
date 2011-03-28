<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/AccountController.php';

class AccountControllerTest extends ControllerTestCase
{
	
	/*
	public function setUp()
	{
		$this->setupDatabase();
		parent::setUp();
	}
	
	public function setupDatabase()
	{
		$db = Zend_Db::factory('adapterName');
		$connection = new Zend_Test_PHPUnit_Db_Connection($db, 'database_schema_name');
		$databaseTester = new Zend_Test_PHPUnit_Db_SimpleTester($connection);
		$dabaseFixture = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
			dirname(__FILE__) . '/_files/initialUserFixture.xml'
		);
		$databaseTester->setupDatabase($databaseFixture);
	}
	*/
	
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
    
    public function testAllActionJson()
    {
    	$this->login();
    	$this->getRequest()
    	    ->setHeader('X-Requested-With', 'XMLHttpRequest')
    	    ->setQuery('format', 'json');
        $this->dispatch('/ot/account/all/');
        $this->markTestIncomplete();
    }
    
    public function testAddAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testEditAction()
    {
    	$this->login();
    	$this->dispatch('/ot/account/edit/accountId/1');
    	$this->assertQueryCount('form#account input[type="text"]', 3);
    	$this->assertQuery('form#account select');
    	$this->assertQuery('form#account input[type="hidden"]');
    	
    	
    	
        $this->markTestIncomplete();
    }
    
    public function textEditActionNonExistantAccount()
    {
    	$this->login();
    	$this->dispatch('/ot/account/edit/accountId/9999999');
    	$this->assertQuery('.ui-state-error');
    }
    
    public function testDeleteAction()
    {
        $this->markTestIncomplete();
        // check db row count is one less
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
    
    
    
    public function testGetAccountWithValidDataReturnsUserArray()
    {
    	$account = new Ot_Account();
    	$accountInfo = $account->getAccount('admin', 'local')->toArray();
    	$this->assertArrayHasKey('username', $accountInfo);
    	$this->assertEquals('admin', $accountInfo['username']);
    }
    
    public function testGetAccountWithInvalidDataReturnsNull()
    {
    	$account = new Ot_Account();
    	$accountInfo = $account->getAccount('thisIsAnInvalidAccount', 'local');
    	$this->assertNull($accountInfo);
    }
    
    public function testGeneratePassword()
    {
    	$account = new Ot_Account();
    	$this->assertRegExp('#^[0-9a-f]{7,20}$#', $account->generatePassword());
    }
    
    public function testGenerateApiCode()
    {
    	$account = new Ot_Account();
    	$this->assertRegExp('#^[0-9a-f]{32}$#', $account->generateApiCode());
    }
    
    /**
     * @expectedException Exception
     */
    public function testVerifyWithInvalidApiCode()
    {
    	$account = new Ot_Account();
    	$account->verify('invalidCode');
    }
    
    public function testVerifyWithValidApiCode()
    {
    	$this->markTestIncomplete();
    }
    
    public function testGetAccountsForRole()
    {
    	$this->markTestSkipped('db changes login time, so skip until fixed');
    	$account = new Ot_Account();
    	$checkAgainst = array(
    		0 => array(
	    		'accountId'    => '31',
	    		'username'     => 'admin',
	    		'realm'        => 'local',
	    		'password'     => '21232f297a57a5a743894a0e4a801fc3',
	    		'apiCode'      => '',
	    		'role'         => '3',
	    		'emailAddress' => 'srgraham@ncsu.edu', 
	    		'firstName'    => 'Admin',
	    		'lastName'     => 'Mcadmin',
	    		'timezone'     => 'America/New_York',
	    		'lastLogin'    => '0'
    		)
    	);
    	/*
    	$adapter = new Zend_Test_DbAdapter();
    	$checkAgainstStatement = Zend_Test_DbStatement::createSelectStatement($checkAgainst);
    	$account->setDefaultAdapter($adapter);
    	//var_dump($account->getAdapter());
    	$adapter->appendStatementToStack($checkAgainstStatement);*/
    	
    	$this->assertSame($checkAgainst, $account->getAccountsForRole(3)->toArray());
    }
    
    public function testAccountFrom()
    {
    	$account = new Ot_Account();
    	$account->form();
    
    }
    
    
}