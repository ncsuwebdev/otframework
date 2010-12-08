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
		$this->dispatch('ot/bug');
		$this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('index');
		$this->assertQueryCount('table.list tr', 2);
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
	
	public function testEditAction()
	{
		$this->markTestIncomplete();
	}
	
	
	public function testAddingBugMakesCountIncrease()
	{
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr', 2);
		
		$this->tearDown();
		$this->setUp(); //reset so assertQueryCount() doesn't add on the 2 from the previous dispatch
		
		$this->addBug();
		
		$this->dispatch('ot/bug');
		$this->assertQueryCount('table.list tr', 3);
	}
	
}