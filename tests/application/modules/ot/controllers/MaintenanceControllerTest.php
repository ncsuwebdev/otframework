<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/MaintenanceController.php';

class MaintenanceControllerTest extends ControllerTestCase
{
	
	public function testIndexWhenMaintenanceModeIsOff()
	{
		$this->login();
		$this->dispatch('/ot/maintenance/index');
		
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('maintenance');
    	$this->assertAction('index');
	}
	
	/**
	 * @expectedException Ot_Exception_Input
	 */
	public function testToggleNoStatusGivesException()
	{
		$this->login();
		$this->dispatch('/ot/maintenance/toggle');
		
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/maintenance');
		$this->assertModule('ot');
    	$this->assertController('maintenance');
    	$this->assertAction('toggle');
	}
	
	public function testToggleOn()
	{
		$this->login();
		$this->dispatch('/ot/maintenance/toggle?status=on');
		
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/maintenance');
		$this->assertModule('ot');
    	$this->assertController('maintenance');
    	$this->assertAction('toggle');
		
		$maintenanceModeFileName = $this->getDefaultProperties('Ot_MaintenanceController', '_maintenanceModeFileName');
		$this->assertFileExists(APPLICATION_PATH . '/../overrides/' . $maintenanceModeFileName);
	}
	
	/**
	 * @depends testToggleOn
	 **/
	public function testIndexActionWhenMaintenanceIsOn()
	{
		// @todo - this order of operations stuff might mess up. think of some better way like manually adding the maintenace file
		$this->login();
		$this->dispatch('/ot/maintenance/index');
		
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('maintenance');
    	$this->assertAction('index');
		
    	$this->assertQueryContentContains('.maintenanceModeOn', 'Site is currently in maintenance and not available to general users.
<a href="/otframework/ot/maintenance">Click Here</a> to disable.');
	}
	
	public function testToggleOff()
	{
		$this->login();
		$this->dispatch('/ot/maintenance/toggle?status=off');
		
		$this->assertResponseCode(302);
		$this->assertRedirectTo('/ot/maintenance');
		$this->assertModule('ot');
    	$this->assertController('maintenance');
    	$this->assertAction('toggle');
		
		$maintenanceModeFileName = $this->getDefaultProperties('Ot_MaintenanceController', '_maintenanceModeFileName');
		$this->assertFileNotExists(APPLICATION_PATH . '/../overrides/' . $maintenanceModeFileName);
	}
	
	/**
	 * @depends testToggleOff
	 **/
	public function testIndexActionWhenMaintenanceIsOff()
	{
		$this->login();
		$this->dispatch('/ot/maintenance/index');
		
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertModule('ot');
    	$this->assertController('maintenance');
    	$this->assertAction('index');
		
    	$this->assertQueryContentContains('.maintenanceModeOff', 'This application is not in maintenance mode.');
	}
	
    
}