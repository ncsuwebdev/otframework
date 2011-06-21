<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/BugController.php';

class BugControllerTest extends ControllerTestCase
{
	
	public static function setUpBeforeClass()
    {
    	parent::setUpBeforeClass();
    	self::setupDatabase();
    }
    
	public function setUp()
	{
		parent::setUp();
		$this->setupDatabase('controllers/bug/ot_bug.xml');
	}
	
	public function addBug() {
		$this->markTestIncomplete();
		$bug = new Ot_Bug();
		$insertData = array(
			'title'           => 'title2',
			'submitDt'        => 55555,
			'reproducibility' => 'always',
			'severity'        => 'minor',
			'priority'        => 'low',
			'status'          => 'new',
			'text'            =>
			array(
				'accountId' => 1,
				'postDt'    => 55555,
				'text'      => 'bug text',
			),
		);
		$bug->insert($insertData);
	}
	
	public function testIndexAction()
	{
		$this->dispatch('ot/bug');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('index');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 1);
		
		$this->getResponse()->setBody('');
		$this->addBug();
		
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 2);
		$this->assertNotRedirect();
		$this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('index');
		
		$this->markTestIncomplete('load xml table to match against');
	}
	
	public function testDetailsActionWithExistingBug()
	{
		$this->login();
		$this->dispatch('ot/bug/details/bugId/1');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('details');
        
		$this->assertQueryContentContains('table.form tr[1] td[2]', 'New');
		$this->assertQueryContentContains('table.form tr[2] td[2]', '12/08/2010 02:51 PM');
		$this->assertQueryContentContains('table.form tr[3] td[2]', 'Always');
		$this->assertQueryContentContains('table.form tr[4] td[2]', 'Minor');
		$this->assertQueryContentContains('table.form tr[5] td[2]', 'Low');
		$this->assertQueryContentContains('table.form tr[6] td[2] div.bugText div.header', 'Submitted by Admin Mcadmin (admin)');
		$this->assertQueryContentContains('table.form tr[6] td[2] div.bugText div.bugContent', 'testasdga description');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testDetailsActionWithoutGetBugIdGivesException()
	{
		$this->login();
		$this->dispatch('ot/bug/details');
	}
	
	/**
	 * @expectedException Ot_Exception_Data
	 */
	public function testDetailsActionOnNonExistantBugIdGivesException()
	{
		$this->login();
		$this->dispatch('ot/bug/details?bugId=-9999');
	}
	
	public function testDeleteAction()
	{
		$this->markTestIncomplete();
	}
	
	public function testAddAction()
	{
		$this->markTestIncomplete();
	}
	
	
	
	public function incompleteDataProvider()
	{
		return array(
			/*  0 */ array('', '', '', '', '', 'missing everything'),
			/*  1 */ array('',      'reproducibility', 'severity', 'priority', 'description', 'missing title'),
			/*  2 */ array('title', 'reproducibility', 'severity', 'priority', '',            'missing description'),
			/*  3 */ array('title', '\'"<>',           'severity', 'priority', 'description', 'invalid reproducibility'),
			/*  4 */ array('title', 'reproducibility', '\'"<>',    'priority', 'description', 'invalid severity'),
			/*  5 */ array('title', 'reproducibility', 'severity', '\'"<>',    'description', 'invalid priority'),
		
			// array('title', 'reproducibility', 'severity', 'priority', 'description', ''),
			
		);
	
	}
	
	

	/**
	 * @dataProvider incompleteDataProvider
	 */
	public function testAddActionIncompleteData($title, $reproducibility, $severity, $priority, $description, $errorMessage)
	{
		$this->request
			->setMethod('POST')
			->setPost(
				array(
					'title'           => $title,
					'reproducibility' => $reproducibility,
					'severity'        => $severity,
					'priority'        => $priority,
					'description'     => $description,
				)
		);
			
		$this->dispatch('/ot/bug/add');
		
		$this->assertNotRedirect();
		$this->assertQuery('#systemMessages');
		$this->assertQuery('.errors', $errorMessage);
	}
	
	
	public function testEditAction()
	{
		$this->markTestIncomplete();
	}
	
	
	public function testAddingBugMakesCountIncrease()
	{
		$this->addBug();
		
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 2);
		
		$this->getResponse()->setBody('');
		$this->addBug();
		
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 3);
	}
	
}