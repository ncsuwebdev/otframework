<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/BugController.php';

class BugControllerTest extends ControllerTestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->setupDatabase('ot_bug.xml');
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
				'accountId' => 31,
				'postDt'    => 55555,
				'text'      => 'bug text',
			),
		);
		$bug->insert($insertData);
	}
	
	public function testIndexAction()
	{
		$this->markTestIncomplete();
		$this->dispatch('ot/bug');
		$this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('index');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 2);
	}
	
	public function testDetailsAction()
	{
		$this->markTestIncomplete();
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
		$this->markTestIncomplete();
		
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 2);
		
		$this->tearDown();
		$this->setUp(); //reset so assertQueryCount() doesn't add on the 2 from the previous dispatch
		
		$this->addBug();
		
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 3);
	}
	
}