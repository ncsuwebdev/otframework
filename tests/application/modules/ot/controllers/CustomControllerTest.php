<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/CustomController.php';

class CustomControllerTest extends ControllerTestCase
{
    
    public function setUp()
    {
        parent::setUp();
        $this->setupDatabase('controllers/custom/custom.xml');
        $this->login();
    }
    
    public function testInit()
    {
        $this->markTestIncomplete();
    }
    
    public function testIndexAction()
    {
        $this->dispatch('ot/custom/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('index');
        
        $this->markTestIncomplete();
    }
    
    public function testDetailsAction()
    {
        $this->dispatch('ot/custom/details/objectId/Ot_Profile');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('details');
        
        $this->markTestIncomplete();
    }
    
    public function testSaveAttributeOrderAction()
    {
        $this->dispatch('ot/custom/save-attribute-order/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('save-attribute-order');
        
        $this->markTestIncomplete();
    }
    
    public function testAttributeDetailsAction()
    {
        $this->markTestIncomplete();
        
        $this->dispatch('ot/custom/attribute-details/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('attribute-details');
        
        $this->markTestIncomplete();
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testAttributeDetailsActionWithInvalidAttributeId()
    {
        $this->dispatch('ot/custom/attribute-details/attributeId/5461151');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testAttributeDetailsActionWithoutAttributeId()
    {
        $this->dispatch('ot/custom/attribute-details/');
    }
    
    public function testAddAction()
    {
        $this->markTestIncomplete();
        
        $this->dispatch('ot/custom/add/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('add');
        
        $this->markTestIncomplete();
    }
    
    public function testEditAction()
    {
        $this->markTestIncomplete();
        
        $this->dispatch('ot/custom/edit/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('edit');
        
        $this->markTestIncomplete();
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testEditActionWithInvalidAttributeId()
    {
        $this->dispatch('ot/custom/edit/attributeId/564512');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testEditActionWithoutAttributeId()
    {
        $this->dispatch('ot/custom/edit/');
    }
    
    public function testDeleteAction()
    {
        $this->markTestIncomplete();
        
        $this->dispatch('ot/custom/delete/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('delete');
        
        $this->markTestIncomplete();
    }
    
}