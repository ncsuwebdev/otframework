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
    
    public function testLoginPageRedirectWhenNotLoggedIn()
    {
        $this->logout();
        $this->dispatch('/ot/account'); //the redir effects the entire ot/ dir, but check ot/account too
        $this->assertResponseCode(200);
        //$this->assertRedirectTo('/ot/login/index');
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('login');
        $this->assertAction('index');
    }
    
    public function testIndexAction()
    {
        $this->login();
        $this->dispatch('/ot/account');
        
        $this->assertResponseCode(200);
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
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('all');
        
        // not much to test here
    
    }
    
    public function allActionJsonProvider()
    {
        return array(
            array('1', '15', 'username', 'asc', '', 'lastName', '',
                array(
                    'page' => 1,
                    'total' => 1,
                    'rows' => array(
                        array(
                            'id' => "1",
                            'cell' => array('admin', 'Admin', 'Mcadmin', 'Local Auth', 'oit_ot_staff'),
                        ) ,
                    ),
                ),
                'filter guests'
            ),
                
                
            array('1', '15', 'username', 'asc', 'guest', 'role', '',
                array(
                    'page' => 1,
                    'total' => 0,
                    'rows' => array(),
                ),
                'filter guests'
            ),
                
                
            array('1', '1', 'username', 'asc', 'a', 'username', '',
                array(
                    'page' => 1,
                    'total' => 0,
                    'rows' => array(),
                ),
                'filter guests'
            ),
        
        
        );
    
    }
    
    /**
     * @dataProvider allActionJsonProvider
     */
    public function testAllActionJson($page, $rp, $sortName, $sortOrder, $query, $qType, $resultCount, $expected, $message)
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
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('/ot/account/all/');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('all');
        
        $results = json_decode($this->getResponse()->getBody(), true);
        
        $this->assertEquals($results['total'], count($results['rows']));
        $this->assertLessThanOrEqual($rp, $results['total']);
        
        $this->assertEquals($expected, $results);
    }
    
    public function testAddAction()
    {
        // @todo - load example table
        //$this->markTestSkipped('Skip this test so it doesn\'t spam my email');
        $this->login();
        
        
        $postData = array(
            'username' => 'GEORGE',
            'realm' => 'local',
            'firstName' => 'GE',
            'lastName' => 'OGRE',
            'emailAddress' => 'srgraham@ncsu.edu',
            'timezone' => 'America/New_York',
            'roleSelect' => 1,
            'submit' => array('Save', 'asdf'),
        );
        
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
        
        $account = new Ot_Account();
        $accounts = $account->fetchAll();
        
        $this->assertObjectHasAttribute('password', $accounts[0]);
        $this->assertObjectHasAttribute('password', $accounts[1]);
        
        // after checking that password does exist, change it since we can't know what it was set at
        $accounts[1]->password = '21232f297a57a5a743894a0e4a801fc3';
        
        $matchAgainst = array(
            (object) array(
                'accountId' => '1',
                'username' => 'admin',
                'realm' => 'local',
                'password' => '21232f297a57a5a743894a0e4a801fc3',
                'apiCode' => '21232f297a57a5a743894a0e4a801fc3',
                'emailAddress' => 'admin@admin.com',
                'firstName' => 'Admin',
                'lastName' => 'Mcadmin',
                'timezone' => 'America/New_York',
                'lastLogin' => '0',
                'role' => array('3'),
            ),
            (object) array(
                'accountId' => '2',
                'username' => 'GEORGE',
                'realm' => 'local',
                'password' => '21232f297a57a5a743894a0e4a801fc3',
                'apiCode' => '',
                'emailAddress' => 'srgraham@ncsu.edu',
                'firstName' => 'Ge',
                'lastName' => 'Ogre',
                'timezone' => 'America/New_York',
                'lastLogin' => '0',
                'role' => array('1'),
            ),
        );
        
        $this->assertEquals($matchAgainst, $accounts);
        
    }
    
    public function testEditActionOnSomeoneElsesAccount()
    {
        $this->login();
        
        $this->setUpDatabase('controllers/account/account_add_account.xml');
        
        $postData = array(
            'accountId' => 2,
            'username' => 'editman',
            'firstName' => 'FIRST',
            'lastName' => 'LAST',
            'emailAddress' => 'srgraham@ncsu.edu',
            'timezone' => 'America/New_York',
            'realm' => 'local',
            'roleSelect' => array(2),
            'submit' => 'Save',
        );
        
        $this->request->setMethod('POST')
            ->setPost($postData);
        
        $this->dispatch('/ot/account/edit/accountId/2');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/account/index/accountId/2');
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('edit');
        
        $this->getResponse()->setBody('');
        
        $this->setUpDatabase('controllers/account/account_add_account.xml');
        
        $postData = array(
            'accountId' => 2,
            'username' => 'editman2',
            'firstName' => 'FIRST',
            'lastName' => 'LAST',
            'emailAddress' => 'srgraham@ncsu.edu',
            'timezone' => 'America/New_York',
            'realm' => 'local',
            'roleSelect' => array(2, 3),
        );
        
        $this->request->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('ot/account/edit/accountId/2');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/account/index/accountId/2');
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('edit');
        
        
        $this->markTestIncomplete();
    }
    
    public function testEditActionOnOwnAccount()
    {
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
    
    /**
     * @expectedException Ot_Exception_Access
     */
    public function testDeleteActionOnSelf()
    {
        $this->login();
        $this->setupDatabase('controllers/account/account_add_account.xml');
        
        $postData = array(
            'deleteButton' => 'Yes, Delete',
        );
        $this->request->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('ot/account/delete/accountId/1');
    }
    
    public function testDeleteActionValid()
    {
        
        $this->setupDatabase('controllers/account/account_add_account.xml');
        
        $this->login();
        
        $postData = array(
            'deleteButton' => 'Yes, Delete',
        );
        $this->request->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('ot/account/delete/?accountId=2');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/account/all');
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('delete');
        
        $this->resetRequest();
        $this->resetResponse();
        $this->request->setPost(array());
        $this->request->setQuery(array());
        //Zend_Registry::set('getFilter', array());
        $get = Zend_Registry::get('getFilter')->setData(array());
        Zend_Registry::set('getFilter', $get);
        
        $this->login();
        
        $postData = array(
            'page' => 1,
            'rp' => 15,
            'sortname' => 'username',
            'sortorder' => 'asc',
            'query' => '',
            'qtype' => 'role',
            'accountId' => 1,
        );
        $this->getRequest()
            ->setHeader('X-Requested-With', 'XMLHttpRequest')
            ->setQuery('format', 'json')
            ->setMethod('POST')
            ->setPost($postData);
        $this->login();
        $this->dispatch('/ot/account/all/?accountId=1');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('account');
        $this->assertAction('all');
        $matchAgainst = array(
            'page' => 1,
            'total' => 1,
            'rows' => array(
                array(
                    'id' => "1",
                    'cell' => array(
                        'admin',
                        'Admin',
                        'Mcadmin',
                        'Local Auth',
                        'oit_ot_staff',
                    ),
                ),
            ),
        );
        $this->assertEquals(json_encode($matchAgainst), $this->getResponse()->getBody());
        
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
        $expectedAccount = (object) array(
            'accountId' => '1',
            'username' => 'admin',
            'realm' => 'local',
            'password' => '21232f297a57a5a743894a0e4a801fc3',
            'apiCode' => '21232f297a57a5a743894a0e4a801fc3',
            'emailAddress' => 'admin@admin.com',
            'firstName' => 'Admin',
            'lastName' => 'Mcadmin',
            'timezone' => 'America/New_York',
            'lastLogin' => '0',
            'role' => array(3),
        );
        $account = new Ot_Account();
        $this->assertEquals($expectedAccount, $account->verify('21232f297a57a5a743894a0e4a801fc3'));
    }
    
    public function testGetAccountsForRole()
    {
        $account = new Ot_Account();
        $checkAgainst = array(
            (object) array(
                'accountId'    => '1',
                'username'     => 'admin',
                'realm'        => 'local',
                'password'     => '21232f297a57a5a743894a0e4a801fc3',
                'apiCode'      => '21232f297a57a5a743894a0e4a801fc3',
                'emailAddress' => 'admin@admin.com',
                'firstName'    => 'Admin',
                'lastName'     => 'Mcadmin',
                'timezone'     => 'America/New_York',
                'lastLogin'    => '0',
                'role'         => array(3),
            ),
        );
        $this->assertEquals($checkAgainst, $account->getAccountsForRole(3));
    }
    
    public function testAccountForm()
    {
        $account = new Ot_Account();
        $account->form();
    
    }
    
    public function testAccountSignupGetsDefaultAttributeOnNewAccount()
    {
        
    
    
    }
    
    
}