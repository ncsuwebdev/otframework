<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/ImageController.php';

class ImageControllerTest extends ControllerTestCase
{

    public function testIndexActionWithNoGet()
    {
        $this->dispatch('/ot/image/index');
        
        $this->assertResponseCode(404);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('image');
        $this->assertAction('index');
        
        $this->assertEquals("", $this->getResponse()->getBody());
    }
    
    public function testIndexActionWithValidImage()
    {
        // @todo - make sure at least one row is in the db 
        $this->dispatch('/ot/image/index?imageId=1');
        
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('image');
        $this->assertAction('index');
        
        // @todo - somehow validate that the Content-Type header was sent correctly;
        //         can't test it because it doesn't get saved to $this anywhere afaik
        //$this->assertEquals("Content-Type: ", $this->getResponse()->getRawHeaders());
    }
    
    
}