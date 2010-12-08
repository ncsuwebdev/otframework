<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/NavController.php';

class NavControllerTest extends ControllerTestCase
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
	
	public function testIndexAction()
	{
		$this->markTestIncomplete();
	}
	
	public function testGetResourcesAction()
	{
		$this->markTestIncomplete();
	}
    
	public function testSaveAction()
	{
		$this->markTestIncomplete();
	}
	
	public function testNavGetNav()
	{
		$this->markTestIncomplete();
	}
    
	public function testNavGenerateHtml()
	{
		
		$navData = array(
			'children' => array(
				array(
					'show' => 1,
					'display' => 'display',
					'parent' => 'parent',
					'id' => '5',
					'module' => 'ot',
					'controller' => 'nav',
					'action' => 'index',
					'link' => 'http://localhost/otframework/ot/nav/',
					'allowed' => '1',
					'target' => 'http://localhost/otframework/ot/nav/',
					'children' => array()
				)
			)
		);
		$nav = new Ot_Nav();
		$html = '<li name="display" id="navItem_parent_5"><a href="http://localhost/otframework/ot/nav/" target="http://localhost/otframework/ot/nav/">display</a>' . "\n" . '</li>';
		$this->assertEquals($nav->generateHtml($navData), $html);
		
		
	}
	
	
	
    
}