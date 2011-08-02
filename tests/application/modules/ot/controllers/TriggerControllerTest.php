<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/TriggerController.php';

class TriggerControllerTest extends ControllerTestCase
{
    
    
    public function testIndexAction()
    {
        // @todo - load some example table with default triggers in it to match against
        $this->login();
        $this->dispatch('ot/trigger');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('index');
        $this->assertQueryCount('table.list tr', 5);
        
        //$this->markTestIncomplete('load xml table to match against');
    }
    
    public function testDetailsAction()
    {
        $this->login();
        $this->dispatch('ot/trigger/details/triggerId/Login_Index_Forgot');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('details');
        
        $this->assertQueryContentContains('table.list tr[2] td[1]', 'User forgot password');
        $this->assertQueryContentContains('table.list tr[2] td[2]', 'Send email');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testDetailsActionWithMissingTriggerIdGivesException()
    {
        $this->login();
        $this->dispatch('ot/trigger/details/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('details');
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testDetailsActionWithInvalidTriggerIdGivesException()
    {
        $this->login();
        $this->dispatch('ot/trigger/details/triggerId/I_AM_AN_INVALID_TRIGGER_ID');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('details');
    }
    
    public function testEditAction()
    {
        $this->login();
        $this->dispatch('ot/trigger/edit/triggerActionId/15');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('edit');
        
        /*
        $this->assertQueryCount('#helper[value=\'Ot_Trigger_Plugin_Email\']', 1);
        $this->assertQueryCount('#name[value=\'User forgot password\']', 1);
        $this->assertQueryCount('#Ot_Trigger_Plugin_Email-to[value=\'[[emailAddress]]\']', 1);
        $this->assertQueryCount('#Ot_Trigger_Plugin_Email-from[value=\'admin@webapps.ncsu.edu\']', 1); // @todo - this right?
        $this->assertQueryCount('#Ot_Trigger_Plugin_Email-subject[value=\'Your password has been reset\']', 1);
        $this->assertQueryContentContains('#Ot_Trigger_Plugin_Email-body', 'Thanks [[firstName]] [[lastName]]    You password for [[username]] has been reset.  Go here [[resetUrl]] to change your password.');
        */
        // Zend's dom querying is wrong for some CSS, so let's use xpath instead
        
        $this->assertQueryContentContains('/input[@id=\'helper\']/@value', 'Ot_Trigger_Plugin_Email', 1);
        $this->assertQueryContentContains('/input[@id=\'name\']/@value', 'User forgot password', 1);
        $this->assertQueryContentContains('/input[@id=\'Ot_Trigger_Plugin_Email-to\']/@value', '[[emailAddress]]', 1);
        $this->assertQueryContentContains('/input[@id=\'Ot_Trigger_Plugin_Email-from\']/@value', 'admin@webapps.ncsu.edu', 1); // @todo - this right?
        $this->assertQueryContentContains('/input[@id=\'Ot_Trigger_Plugin_Email-subject\']/@value', 'Your password has been reset', 1);
        
        // @todo: this has a typo in it
        $this->assertQueryContentContains('#Ot_Trigger_Plugin_Email-body', 'Thanks [[firstName]] [[lastName]]    You password for [[username]] has been reset.  Go here [[resetUrl]] to change your password.');
        
        $this->assertQueryContentContains('table.list tr[2] td[1]', '[[firstName]]');
        $this->assertQueryContentContains('table.list tr[2] td[2]', 'First name of the user');
        
        $this->assertQueryContentContains('table.list tr[3] td[1]', '[[lastName]]');
        $this->assertQueryContentContains('table.list tr[3] td[2]', 'Last name of the user.');
        
        $this->assertQueryContentContains('table.list tr[4] td[1]', '[[emailAddress]]');
        $this->assertQueryContentContains('table.list tr[4] td[2]', 'Email address of the user.');
        
        $this->assertQueryContentContains('table.list tr[5] td[1]', '[[username]]');
        $this->assertQueryContentContains('table.list tr[5] td[2]', 'Username of user.');
        
        $this->assertQueryContentContains('table.list tr[6] td[1]', '[[loginMethod]]');
        $this->assertQueryContentContains('table.list tr[6] td[2]', 'Name of login method which they use to log into the system with.');
        
        $this->assertQueryContentContains('table.list tr[7] td[1]', '[[resetUrl]]');
        $this->assertQueryContentContains('table.list tr[7] td[2]', 'URL the user will need to go to to reset their password.');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testEditActionWithMissingTriggerGivesException()
    {
        $this->login();
        $this->dispatch('ot/trigger/edit/');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('edit');
    
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testEditActionWithInvalidTriggerGivesException()
    {
        $this->login();
        $this->dispatch('ot/trigger/edit/triggerActionId/111111111');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('trigger');
        $this->assertAction('edit');
    
    }
    
}