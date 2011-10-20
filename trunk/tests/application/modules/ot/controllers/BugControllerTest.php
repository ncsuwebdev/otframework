<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/BugController.php';

class BugControllerTest extends ControllerTestCase
{
    
    public function setUp()
    {
        parent::setUp();
        $this->setupDatabase('controllers/bug/bug.xml');
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
                'accountId' => 1,
                'postDt'    => 55555,
                'text'      => 'bug text',
            ),
        );
        $bug->insert($insertData);
    }
    
    public function testIndexAction()
    {
        $this->dispatch('ot/bug');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('index');
        $this->assertQueryCount('table.list tr.row1, table.list tr.row2', 1);
        
        $this->getResponse()->setBody('');
        $this->addBug();
        
        $this->dispatch('ot/bug');
        $this->assertQueryCount('table.list tr.row1, table.list tr.row2', 2);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('index');
        
        $this->markTestIncomplete('load xml table to match against');
    }
    
    public function testDetailsActionWithExistingBug()
    {
        $this->login();
        $this->dispatch('ot/bug/details/bugId/1');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('details');
        
        $this->assertQueryContentContains('table.form tr[1] td[2]', 'New');
        $this->assertQueryContentContains('table.form tr[2] td[2]', '12/08/2010 02:51 PM');
        $this->assertQueryContentContains('table.form tr[3] td[2]', 'Always');
        $this->assertQueryContentContains('table.form tr[4] td[2]', 'Minor');
        $this->assertQueryContentContains('table.form tr[5] td[2]', 'Low');
        $this->assertQueryContentContains('table.form tr[6] td[2] div.bugText div.header', 'Submitted by Admin Mcadmin (admin)');
        $this->assertQueryContentContains('table.form tr[6] td[2] div.bugText div.bugContent', 'testasdga description');
    }
    
    /**
     * @expectedException Ot_Exception_Input
     */
    public function testDetailsActionWithoutGetBugIdGivesException()
    {
        $this->login();
        $this->dispatch('ot/bug/details');
    }
    
    /**
     * @expectedException Ot_Exception_Data
     */
    public function testDetailsActionOnNonExistantBugIdGivesException()
    {
        $this->login();
        $this->dispatch('ot/bug/details?bugId=-9999');
    }
    
    public function testDeleteAction()
    {
        $this->login();
        $postData = array(
            'deleteButton' => 'Yes, Delete',
        );
        
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('ot/bug/delete/bugId/1');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/bug');
        $this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('delete');
        
        $this->assertEquals($this->getResponse()->getBody(), '');
        
        $this->resetRequest();
        $this->resetResponse();
        
        $this->dispatch('ot/bug/index');
        $this->assertQueryCount('table.list tr td.noResults', 1);
        
        var_dump($this->getResponse()->getBody());exit;
    }
    
    public function testAddAction()
    {
        $this->login();
        $postData = array(
            'title' => 'Bug Title',
            'reproducibility' => 'always',
            'severity' => 'minor',
            'priority' => 'low',
            'description' => 'description',
        );
        
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('ot/bug/add');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/bug/details/bugId/2');
        $this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('add');
        
        $this->markTestIncomplete();
    }
    
    public function incompleteDataProvider()
    {
        return array(
            /*  0 */ array('', '', '', '', '', '', 'missing everything'),
            /*  1 */ array('', 'new',      'always', 'minor', 'low', 'description', 'missing title'),
            /*  2 */ array('title', 'new', 'always', 'minor', 'low', '',            'missing description'),
            /*  3 */ array('title', 'new', '\'"<>',           'minor', 'low', 'description', 'invalid reproducibility'),
            /*  4 */ array('title', 'new', 'always', '\'"<>',    'low', 'description', 'invalid severity'),
            /*  5 */ array('title', 'new', 'always', 'minor', '\'"<>',    'description', 'invalid priority'),
        
            // array('title', 'status', 'reproducibility', 'severity', 'priority', 'description', ''),
            
        );
    
    }
    
    

    /**
     * @dataProvider incompleteDataProvider
     */
    public function testAddActionIncompleteData($title, $status, $reproducibility, $severity, $priority, $description, $errorMessage)
    {
        // ignore status; it's there so that the dataprovider can be reused for the edit action
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
        $this->login();
        $postData = array(
            'title' => 'EDIT',
            'status' => 'fixed',
            'reproducibility' => 'never',
            'severity' => 'crash',
            'priority' => 'critical',
            'description' => 'DESCRIPT TWOOOOO',
        );
        $this->request
            ->setMethod('POST')
            ->setPost($postData);
        
        $this->dispatch('/ot/bug/edit/bugId/1');
        
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/ot/bug/details/bugId/1');
        $this->assertModule('ot');
        $this->assertController('bug');
        $this->assertAction('edit');
        
    
    }
    
    /**
     * @dataProvider incompleteDataProvider
     */
    public function testEditActionIncompleteData($title, $status, $reproducibility, $severity, $priority, $description, $errorMessage)
    {
        $this->request
            ->setMethod('POST')
            ->setPost(
                array(
                    'title'           => $title,
                    'status'          => $status,
                    'reproducibility' => $reproducibility,
                    'severity'        => $severity,
                    'priority'        => $priority,
                    'description'     => $description,
                )
        );
            
        $this->dispatch('/ot/bug/edit/bugId/1');
        
        $this->assertNotRedirect();
        $this->assertQuery('#systemMessages');
        $this->assertQuery('.errors', $errorMessage);
    }
    
    
    public function testAddingBugMakesCountIncrease()
    {
        $this->addBug();
        
        $this->dispatch('ot/bug');
        $this->assertQueryCount('table.list tr.row1, table.list tr.row2', 2);
        
        $this->getResponse()->setBody('');
        $this->addBug();
        
        $this->dispatch('ot/bug');
        $this->assertQueryCount('table.list tr.row1, table.list tr.row2', 3);
    }
    
}