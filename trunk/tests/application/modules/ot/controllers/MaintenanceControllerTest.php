<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/MaintenanceController.php';

class MaintenanceControllerTest extends ControllerTestCase
{
    public function _toggleMaintenance($onOff)
    {
        $maintenanceModeFileName = $this->getDefaultProperties('Ot_MaintenanceController', '_maintenanceModeFileName');
        if(!$maintenanceModeFileName) {
            $this->fail('Maintenance mode file name invalid.');
        }
        $filepath = APPLICATION_PATH . '/../overrides/' . $maintenanceModeFileName;
        
        
        if($onOff == 'on') {
            if(!is_file($filepath)) {
                fopen($filepath, 'w');
            }
        } elseif($onOff == 'off') {
            if(is_file($filepath)) {
                unlink($filepath);
            }
        }
    }
    
    public function testIndexWhenMaintenanceModeIsOff()
    {
        $this->_toggleMaintenance('off');
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
    }
    
    public function testToggleOn()
    {
        $this->_toggleMaintenance('off');
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
    
    public function testIndexActionWhenMaintenanceIsOn()
    {
        $this->_toggleMaintenance('on');
        $this->login();
        $this->dispatch('/ot/maintenance/index');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('maintenance');
        $this->assertAction('index');
        $this->assertQueryContentContains('.maintenanceModeOn', 'Site is currently in maintenance and not available to general users.');
        $this->assertQueryContentContains('.maintenanceModeOn', '<a href="/ot/maintenance">Click Here</a> to disable.');
    }
    
    public function testToggleOff()
    {
        $this->_toggleMaintenance('on');
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