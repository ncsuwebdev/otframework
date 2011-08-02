<?php
require_once 'Zend/Application.php';
require_once TESTS_PATH . '/application/DatabaseTestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';

class AccountDatabaseTest extends DatabaseTestCase
{
    protected $_account;
    
    protected function setUp()
    {
        parent::setUp();
        $account = new Ot_Account();
        $this->_account = new Ot_Account();
        //var_dump($this->_account->fetchAll()->toArray());
    }
    
    protected function tearDown()
    {
        // @todo - is it okay to do this to reset this table at the end of the tests?
        //         Also, it doesn't seem to work all the time.
        // $this->getAdapter()->query('TRUNCATE TABLE ot_tbl_ot_account;');
        $this->_account = null;
    }
    public function testTableIsEmptyAtConstruct()
    {
        $this->assertType('Ot_Account', $this->_account);
        $this->assertEquals(0, count($this->_account->fetchAll()->toArray()), 'Initial DB for account is not empty');
    }
    
    public function testDatabaseCanBeRead()
    {
        $this->dbXmlCompare('ot_account.xml', 'tbl_ot_account', array('accountId', 'lastLogin'));
        
        /*
        $xmlSet = $this->createFlatXmlDataSet(TESTS_PATH . '/_files/ot_account.xml');
        $dbset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($this->getConnection()->createDataSet(), array('ot_tbl_ot_account' => array('accountId', 'lastLogin')));
        $this->assertTablesEqual($xmlSet->getTable('ot_tbl_ot_account'), $dbset->getTable('ot_tbl_ot_account'));
        /*exit;
        
        /*
        $ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet(
            $this->getConnection()
        );
        $ds->addTable('ot_tbl_ot_account', 'SELECT * FROM ot_tbl_ot_account');
        
        $fset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($this->getConnection()->createDataSet(), array('ot_tbl_ot_account' => array('accountId', 'lastLogin')));
        $ds = $fset->getTable('ot_tbl_ot_account');
        
        $xmlSet = $this->createFlatXmlDataSet(
            TESTS_PATH . '/_files/ot_account.xml'
        );
        $xmlSet = $xmlSet->getTable('ot_tbl_ot_account');
        
        
        /*
        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter(
            $dataSet, array('ot_tbl_ot_account' => array('accountId', 'lastLogin'))
        );
        $this->assertDataSetsEqual($xmlSet, $ds);
        */
    
    }
    
    public function accountProvider()
    {
        
        
         return array(
                    'username'     => "srgraham",
                    'password'     => "21232f297a57a5a743894a0e4a801fc3",
                    'realm'        => "local",
                    'firstName'    => "Scott",
                    'lastName'     => "Graham",
                    'emailAddress' => "srgraham@ncsu.edu",
                    'timezone'     => "America/New_York",
                    'role'         => "3",
                );   
        
        
        
        return array (
            array( array(
                'accountId'    => "32",
                'username'     => "srgraham",
                'realm'        => "local",
                'password'     => "21232f297a57a5a743894a0e4a801fc3",
                'apiCode'      => "",
                'role'         => "3",
                'emailAddress' => "srgraham@ncsu.edu",
                'firstName'    => "Scott",
                'lastName'     => "Graham",
                'timezone'     => "America/New_York",
                'lastLogin'    => "1291662253",
            ))
        );
    }
    
    
    /** integration testing **/
    

    
    public function testNewEntryPopulatesDatabase()
    {
        $data = $this->accountProvider();
        $this->_account->insert($data);
        
        
        
        $this->dbXmlCompare('ot_account_add_account.xml', 'tbl_ot_account', array('accountId', 'lastLogin'));
        /*
        
        $ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet(
            $this->getConnection()
        );
        $ds->addTable('ot_tbl_ot_account', 'SELECT username, realm, password, apiCode, role, emailAddress, firstName, lastName, timezone FROM ot_tbl_ot_account');
        $dataSet = $this->createFlatXmlDataSet(
            TESTS_PATH . '/_files/ot_account.xml'
        );
        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter(
            $dataSet, array('ot_tbl_ot_account' => array('accountId', 'lastLogin'))
        );
        $this->assertDataSetsEqual($filteredDataSet, $ds);
    /*
        $data = $this->gbEntryProvider();
        foreach ($data as $row) {
            $entry = new Application_Model_GuestbookEntry($row[0]);
            $entry->save();
            unset ($entry);
        }
        $ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet(
            $this->getConnection()
        );
        $ds->addTable('gbentry', 'SELECT * FROM gbentry');
        $dataSet = $this->createFlatXmlDataSet(
                TEST_PATH . "/_files/addedTwoEntries.xml");
        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter(
            $dataSet, array('gbentry' => array('id')));
        $this->assertDataSetsEqual($filteredDataSet, $ds);*/
    }
}