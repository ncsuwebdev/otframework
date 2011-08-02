<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/AccountController.php';

class AccountControllerTest extends ControllerTestCase
{
    // @todo - do testing stuff when there are a few custom_attributes
    public function setUp()
    {
        parent::setUp();
        $this->setupDatabase('controllers/account/account.xml');
    }
    
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
        $this->login();
        $this->dispatch('ot/account/all');
        
    
    }
    
    public function allActionJsonProvider()
    {
        return array(
            array('1', '15', 'username', 'asc', 'guest', 'role', 'filter guests'),
            array('1', '1', 'username', 'asc', 'a', 'username', 'filter guests'),
        
        
        );
    
    }
    
    /**
     * @dataProvider allActionJsonProvider
     */
    public function testAllActionJson($page, $rp, $sortName, $sortOrder, $query, $qType, $message)
    {
        $this->login();
        
        $postData = array(
            'page' => $page,
            'rp' => $rp,
            'sortname' => $sortName,
            'sortorder' => $sortOrder,
            'query' => $query,
            'qtype' => $qType,
        );
        
        $this->getRequest()
            ->setHeader('X-Requested-With', 'XMLHttpRequest')
            ->setQuery('format', 'json')
            ->setPost($postData);
        $this->dispatch('/ot/account/all/');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect('/');
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('all');
        
        $results = json_decode($this->getResponse()->getBody(), true);
        
        $this->assertEquals($results['total'], count($results['rows']));
        $this->assertLessThanOrEqual($rp, $results['total']);
        
        
        $this->markTestIncomplete();
    }
    
    /**
     * @depends testEditAction
     * for some reason having testEditAction follow testAddAction causes a terrible memory leak or something under certain database tables
     * testing goes from 20 minutes to 2-3 minutes if you switch the order.
     * tried to figure out why; mysql timeouts during the postDispatch for frontController plugin activeUsers for some reason
     */
    public function testAddAction()
    {
        // @todo - load example table
        $this->markTestSkipped('Skip this test so it doesn\'t spam my email');
        $this->login();
        
        $postData = array(
            'username' => 'GEORGE',
            'realm' => 'local',
            'firstName' => 'george',
            'lastName' => 'asdf',
            'emailAddress' => 'srgraham@ncsu.edu',
            'timezone' => 'America/New_York',
            'roleSelect' => 1,
            'submit' => array('Save', 'asdf'),
        );
        
        $this->login();
        
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('/ot/account/add/realm/local');
        $this->getResponse();
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/account/all');
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('add');
        
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
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testEditActionNonExistantAccount()
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
        $accountInfo = $account->getAccount('admin', 'local');
        $this->assertObjectHasAttribute('username', $accountInfo);
        $this->assertEquals('admin', $accountInfo->username);
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