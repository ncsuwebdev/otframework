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
    
    public function testIndexAction()
    {
        $this->dispatch('ot/custom/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('index');
        
        $this->assertQueryCount('table.list tbody tr', 1);
        
        $this->assertQueryContentContains('table.list tbody tr td', '<a href="/ot/custom/details/objectId/Ot_Profile">Ot_Profile</a>');
    }
    
    public function testDetailsAction()
    {
        $this->dispatch('ot/custom/details/objectId/Ot_Profile');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('details');
        
        $this->assertQueryContentContains('a.attributeLabel', 'label');
        $this->assertQueryContentContains("a[href='/ot/custom']", 'Back to Object List');
        $this->assertQueryContentContains("a[href='/ot/custom/add/objectId/Ot_Profile']", 'Add New Custom Attribute To Ot_Profile');
        $this->assertQueryContentContains('button#saveButton', 'Save Attribute Order');
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testDetailsActionWithInvalidAttributeId()
    {
        $this->dispatch('ot/custom/details/objectId/I_AM_AN_INVALID_OBJECT');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testDetailsActionWithoutAttributeId()
    {
        $this->dispatch('ot/custom/details/');
    }
    
    public function testSaveAttributeOrderAction()
    {
        //$this->markTestSkipped('I think resetRequest() doesnt reset body right if the first dispatch isnt a redirect');
        
        $this->setupDatabase('controllers/custom/custom_with_multiple_attributes.xml');
        
        $this->dispatch('ot/custom/details/objectId/Ot_Profile');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('details');
        
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1] a', 'attr1');
        $this->assertQueryContentContains('ul#attributeList li[2] tr td[1] a', 'attr2');
        $this->assertQueryContentContains('ul#attributeList li[3] tr td[1] a', 'attr3');
        $a = $this->getResponse();
        $this->resetRequest();
        $this->resetResponse();
        
        $this->login();
        
        $postData = array(
            'objectId' => 'Ot_Profile',
            'attributeIds' => array(
                'attribute_3',
                'attribute_1',
                'attribute_2',
            ),
        );
        $this->request->setMethod('post')
                      ->setPost($postData);
        
        
        $this->dispatch('ot/custom/save-attribute-order/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('save-attribute-order');
        
        
        $this->getResponse()->clearBody();
        $this->getResponse()->setBody(null);
        $this->resetRequest();
        $this->resetResponse();
        
        $this->login();
        
        $this->dispatch('ot/custom/details/objectId/Ot_Profile');
        $b = $this->getResponse();
        
        $this->assertEquals($a, $b);
        
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('details');
        
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1] a', 'attr3');
        $this->assertQueryContentContains('ul#attributeList li[2] tr td[1] a', 'attr1');
        $this->assertQueryContentContains('ul#attributeList li[3] tr td[1] a', 'attr2');
        
        $this->markTestIncomplete();
    }
    
    public function testAttributeDetailsAction()
    {
        $this->dispatch('ot/custom/attribute-details/attributeId/1');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('attribute-details');
        
        $this->assertQueryContentContains('table.form[1] tr[1] td[2]', 'Ot_Profile');
        
        $this->assertQueryContentContains('table.form[2] tr[1] td[2]', 'label');
        $this->assertQueryContentContains('table.form[2] tr[2] td[2]', 'textarea');
        $this->assertQueryContentContains('table.form[2] tr[3] td[2]', 'Yes');
        $this->assertQueryContentContains('table.form[2] tr[4] td[2]', 'Vertical');
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
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testAttributeDetailsActionWithInvalidObjectInfo()
    {
        $this->setupDatabase('controllers/custom/custom_with_invalid_custom_field.xml');
        $this->dispatch('ot/custom/attribute-details/attributeId/1');
    }
    
    public function testAddAction()
    {
        $postData = array(
            'label' => 'hjfjb',
            'type' => 'radio',
            'option' => array('ONE', 'TWO', 'THREE'),
            'required' => '0',
            'direction' => 'horizontal',
        );
        
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost($postData);
        
        $this->dispatch('ot/custom/add/objectId/Ot_Profile');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/custom/details/objectId/Ot_Profile');
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('add');
        
        $this->resetRequest();
        $this->resetResponse();
        
        $this->dispatch('/ot/custom/details/objectId/Ot_Profile');
        
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1] a', 'label');
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1]', '(textarea)');
        
        $this->assertQueryContentContains('ul#attributeList li[2] tr td[1] a', 'hjfjb');
        $this->assertQueryContentContains('ul#attributeList li[2] tr td[1]', '(radio)');
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testAddActionWithInvalidAttributeId()
    {
        $this->dispatch('ot/custom/add/objectId/GEORGE');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testAddActionWithoutAttributeId()
    {
        $this->dispatch('ot/custom/add/');
    }
    
    public function testEditAction()
    {
        $postData = array(
            'label' => 'edit!',
            'type' => 'checkbox',
            'required' => '0',
            'direction' => 'horizontal',
        );
        
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($postData);
        
        $this->dispatch('ot/custom/edit/attributeId/1');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/custom/details/objectId/Ot_Profile');
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('edit');
        
        $this->resetRequest();
        $this->resetResponse();
        $this->login();
        
        $this->dispatch('/ot/custom/details/objectId/Ot_Profile');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('details');
        
        $this->assertQueryCount('ul#attributeList li', 1);
        
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1] a', 'edit!');
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1]', 'checkbox');
        
    }
    
    public function testEditActionWithOptions()
    {
        $postData = array(
            'label' => 'edit!',
            'type' => 'radio',
            'required' => '0',
            'direction' => 'horizontal',
            'option' => array('a', 'b', 'c',),
        );
        
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($postData);
        
        $this->dispatch('ot/custom/edit/attributeId/1');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/custom/details/objectId/Ot_Profile');
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('edit');
        
        $this->resetRequest();
        $this->resetResponse();
        $this->login();
        
        $this->dispatch('/ot/custom/details/objectId/Ot_Profile');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('details');
        
        $this->assertQueryCount('ul#attributeList li', 1);
        
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1] a', 'edit!');
        $this->assertQueryContentContains('ul#attributeList li[1] tr td[1]', 'radio');
    }
    
    public function testEditActionOptionDelete()
    {
        $this->setupDatabase('controllers/custom/custom_with_attribute_with_option.xml');
        $postData = array(
            'label' => 'poll not checkbox',
            'type' => 'radio',
            'required' => '0',
            'direction' => 'horizontal',
            'opt_delete' => array('2', '3', '4', '5'),
            'option' => array('0'),
        );
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($postData);
        $this->dispatch('/ot/custom/edit/attributeId/1');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/custom/details/objectId/Ot_Profile');
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('edit');
        
        $this->resetRequest();
        $this->resetResponse();
        $this->login();
        
        $this->dispatch('/ot/custom/edit/attributeId/1');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('edit');
        
        $this->assertQueryContentContains('form#edit tr[1] td[2]', 'Ot_Profile');
        $this->assertQueryContentContains('form#edit table.form[2] tr[1] td[2]', 'poll not checkbox');
        $this->assertQueryContentContains('form#edit table.form[2] tr[2] td[2] select option[selected="selected"]', 'radio');
        $this->assertQueryContentContains('form#edit table.form[2] tr[2] td[2] div#opt b[1]', 'Delete "1"');
        $this->assertQueryContentContains('form#edit table.form[2] tr[2] td[2] div#opt b[2]', 'Delete "0"');
        $this->assertQueryCount('form#edit table.form[2] tr[3] td[2] input[value="1"]', 1);
        $this->assertQueryCount('form#edit table.form[2] tr[4] td[2] label[1] input[checked="checked"]', 0);
        $this->assertQueryCount('form#edit table.form[2] tr[4] td[2] label[2] input[checked="checked"]', 1);
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
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testEditActionWithInvalidObjectId()
    {
        $this->setupDatabase('controllers/custom/custom_with_invalid_custom_field.xml');
        $this->login();
        $this->dispatch('ot/custom/edit/attributeId/1');
    }
    
    public function testDeleteAction()
    {
        $this->setupDatabase('controllers/custom/custom_with_accounts.xml');
        
        $attributeValue = new Ot_Custom_Attribute_Value();
        $values = $attributeValue->fetchAll('attributeId = 1');
        $this->assertEquals(count($values), 2);
        
        $postData = array(
            'deleteButton' => 'Yes, Delete',
        );
        
        $this->request->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('ot/custom/delete/attributeId/1');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/custom/details/objectId/Ot_Profile');
        $this->assertModule('ot');
        $this->assertController('custom');
        $this->assertAction('delete');
        
        $attributeValue = new Ot_Custom_Attribute_Value();
        $values = $attributeValue->fetchAll('attributeId = 1');
        $this->assertEquals(count($values), 0);
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testDeleteActionWithInvalidAttributeId()
    {
        $this->dispatch('ot/custom/delete/attributeId/564512');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testDeleteActionWithoutAttributeId()
    {
        $this->dispatch('ot/custom/delete/');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testDeleteActionWithInvalidCustomField()
    {
        $this->setupDatabase('controllers/custom/custom_with_invalid_custom_field.xml');
        $this->dispatch('ot/custom/delete/attributeId/1');
    }
    
    
    
}