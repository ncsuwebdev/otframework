<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/AclController.php';

class AclControllerTest extends ControllerTestCase
{
	
	public function testIndexAction()
	{
		$this->login();
		// @todo - load some example table with default accounts/roles in it to match against
		$this->dispatch('ot/acl/index');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('index');
		$this->assertQueryCount('table.list tr.row1, table.list tr.row2', 5);
		
		$this->markTestIncomplete('load xml table to match against');
	}
	
	public function testDetailsAction()
	{
		$this->login();
		
		// @todo - load some example data into the db for the guest role 
		
		$this->dispatch('ot/acl/details/roleId/1');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('details');
		$this->assertQueryCount('#tabs-application .aclSection', 3);
		$this->assertQueryCount('#tabs-application .aclSection[1] table.list tr.controller', 1);
		$this->assertQueryCount('#tabs-application .aclSection[2] table.list tr.controller', 2);
		$this->assertQueryCount('#tabs-application .aclSection[3] table.list tr.controller', 25);
		
		$this->assertQueryCount('#tabs-application tr.controller', 28);
		$this->assertQueryCount('#tabs-application tr.controller td.access', 5);
		
		
		$this->assertQueryCount('#tabs-remote table.list', 1);
		$this->assertQueryCount('#tabs-remote table.list tr.controller', 10);
		
		$this->assertQueryCount('#tabs-remote table.list tr.controller', 10);
		$this->assertQueryCount('#tabs-remote table.list tr.controller td.access', 2);
		
		$this->markTestIncomplete('load xml table to match against');
	}
	
	
    
    
}