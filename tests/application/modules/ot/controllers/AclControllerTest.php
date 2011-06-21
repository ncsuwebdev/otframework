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
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testDetailsActionWithoutRoleSet()
	{
		$this->login();
		$this->dispatch('ot/acl/details/');
	}
	
	/**
	 * @expectedException Ot_Exception_Data
	 */
	public function testDetailsActionWithInvalidRole()
	{
		$this->login();
		$this->dispatch('ot/acl/details/roleId/54312168');
	}
	
	public function testAddAction()
	{
		$this->login();
		// @todo - load some example data into the db
		
		$postData = array(
			'name' => 'Test',
			'inheritRoleId' => 0,
		);
		$this->request
			->setMethod('POST')
			->setPost($postData);
		$this->dispatch('ot/acl/add/');
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/acl/details?roleId=21'); // @todo change this to redir to the correct id
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('add');
	}
	
	public function testEditAction()
	{
		$this->login();
		// @todo - load some example data into the db
		
		$postData = array(
			'name' => 'testRename',
			'inheritRoleId' => 0,
		);
		
		$this->request
			->setMethod('POST')
			->setPost($postData);
		
		$this->dispatch('ot/acl/edit/roleId/22');
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/acl/details/?roleId=22'); // @todo change this redir based on id in example table that's loaded
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('edit');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testEditActionWithoutRoleIdSet()
	{
		$this->login();
		$this->dispatch('ot/acl/edit/');
		$this->assertResponseCode(200);
	}
	
	/**
	 * @expectedException Ot_Exception_Access
	 */
	public function testEditActionWithLockedRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/edit/roleId/2');
	}
	
	/**
	 * @expectedException Ot_Exception_Data
	 */
	public function testEditActionWithInvalidRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/edit/roleId/58642155');
	}
	
	
	public function testApplicationAccessAction()
	{
		$this->login();
		// @todo - load some example data into the db
		
		$postData = array(
			'cron' => array(
				'index' => array(
					'all' => 'allow'
				)
			),
			'ot' => array(
				'api' => array(
					'all' => 'allow'
				)
			),
		);
		
		$this->request
			->setMethod('POST')
			->setPost($postData);
		$this->dispatch('ot/acl/application-access/roleId/23');
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/acl/details/?roleId=23');
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('application-access');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testApplicationAccessActionWithoutRoleIdSet()
	{
		$this->login();
		$this->dispatch('ot/acl/application-access/');
	}
	
	/**
	 * @expectedException Ot_Exception_Access
	 */
	public function testApplicationAccessActionOnLockedRole()
	{
		$this->login();
		// @todo - load example table
		$this->dispatch('ot/acl/application-access/roleId/2');
	}
	
	/**
	 * @expectedException Ot_Exception_Data
	 */
	public function testApplicationAccessActionWithInvalidRole()
	{
		$this->login();
		// @todo - load example table
		$this->dispatch('ot/acl/application-access/roleId/254681');
	}
	
	public function testRemoteAccessAction()
	{
		$this->login();
		// @todo - load some example data into the db
		$postData = array(
			'access' => array(
				'getVersions' => 'allow',
				'describe' => 'allow',
			),
		);
		$this->request
			->setMethod('POST')
			->setPost($postData);
		$this->dispatch('ot/acl/remote-access/roleId/24');
		$this->assertResponseCode(200);
		$this->assertRedirectTo('/ot/acl/details/?roleId=24');
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('remote-access');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testRemoteAccessActionWithoutRoleIdSet()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/remote-access/');
	}
	
	/**
	 * @expectedException Ot_Exception_Data
	 */
	public function testRemoteAccessActionWithInvalidRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/remote-access/roleId/54561');
	}
	
	/**
	 * @expectedException Ot_Exception_Access
	 */
	public function testRemoteAccessActionWithLockedRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/remote-access/roleId/2');
	}
	
	public function testDeleteAction()
	{
		$this->login();
		// @todo - load some example data into the db
		// example data should include a bunch of accounts from role 21 in order to test more stuff
		$postData = array(
			'deleteButton' => 'Yes, Delete',
		);
		
		$this->request
			->setMethod('POST')
			->setPost($postData);
		
		$this->dispatch('ot/acl/delete/roleId/21');
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/acl/');
		$this->assertModule('ot');
        $this->assertController('acl');
        $this->assertAction('index');
	}
	
	/**
	 * @expectedException Ot_Exception_Data
	 */
	public function testDeleteActionOnDefaultRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/delete/roleId/1');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testDeleteActionWithoutRoleIdSet()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/delete/');
	}
	
	/**
	 * @expectedException Ot_Exception_Access
	 */
	public function testDeleteActionOnLockedRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/delete/roleId/2');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testDeleteActionOnNonExistantRole()
	{
		$this->login();
		// @todo - load some example data into the db
		$this->dispatch('ot/acl/delete/roleId/313219');
	}
	
	
	
	
    
    
}