<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
require_once APPLICATION_PATH . '/modules/ot/controllers/NavController.php';

class NavControllerTest extends ControllerTestCase
{
    
    public function testInit()
    {
        $this->markTestIncomplete('How do you test functions that aren\'t actions?');
    }
    
    public function testIndexAction()
    {
        $this->login();
        
        $this->dispatch('ot/nav/index');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('nav');
        $this->assertAction('index');
        
        $this->markTestIncomplete('Not sure how to test here....');
    }
    
    public function testGetResourcesAction()
    {
        // @todo - load table data from example xml
        // @todo - the huge array in here should probably be loaded in xml as well
        $this->login();
        
        $this->dispatch('ot/nav/get-resources');
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertModule('ot');
        $this->assertController('nav');
        $this->assertAction('get-resources');
        
        $data = json_decode($this->getResponse()->getBody(), true);
        
        $matchAgainst = array(
            'modules'=>array(
                'cron'=>array(
                    'name'=>'cron',
                    'controllers'=>array(
                        'index'=>array(
                            'name'=>'index',
                            'actions'=>array(
                                'email-queue',
                            ),
                        ),
                    ),
                ),
                'default'=>array(
                    'name'=>'default',
                    'controllers'=>array(
                        'error'=>array(
                            'name'=>'error',
                            'actions'=>array(
                                'error'
                            ),
                        ),
                        'index'=>array(
                            'name'=>'index',
                            'actions'=>array(
                                'index',
                            ),
                        ),
                    ),
                ),
                'ot'=>array(
                    'name'=>'ot',
                    'controllers'=>array(
                        'account'=>array(
                            'name'=>'account',
                            'actions'=>array(
                                'add',
                                'all',
                                'change-password',
                                'change-user-role',
                                'delete',
                                'edit',
                                'edit-all-accounts',
                                'get-permissions',
                                'index',
                                'revoke-connection',
                            ),
                        ),
                        'acl'=>array(
                            'name'=>'acl',
                            'actions'=>array(
                                'add',
                                'application-access',
                                'delete',
                                'details',
                                'edit',
                                'index',
                                'remote-access',
                            ),
                        ),
                        'activeusers'=>array(
                            'name'=>'activeusers',
                            'actions'=>array(
                                'index',
                            ),
                        ),
                        'api'=>array(
                            'name'=>'api',
                            'actions'=>array(
                                'index',
                                'json',
                                'sample',
                                'soap',
                                'xml',
                            ),
                        ),
                        'auth'=>array(
                            'name'=>'auth',
                            'actions'=>array(
                                'edit',
                                'index',
                                'save-adapter-order',
                                'toggle'
                            ),
                        ),
                        'backup'=>array(
                            'name'=>'backup',
                            'actions'=>array(
                                'download-all-sql',
                                'index',
                            ),
                        ),
                        'bug'=>array(
                            'name'=>'bug',
                            'actions'=>array(
                                'add',
                                'delete',
                                'details',
                                'edit',
                                'index',
                            ),
                        ),
                        'cache'=>array(
                            'name'=>'cache',
                            'actions'=>array(
                                'clear',
                                'index',
                            ),
                        ),
                        'config'=>array(
                            'name'=>'config',
                            'actions'=>array(
                                'edit',
                                'index',
                            ),
                        ),
                        'cron'=>array(
                            'name'=>'cron',
                            'actions'=> array(
                                'index',
                                'toggle',
                            ),
                        ),
                        'custom'=>array(
                            'name'=>'custom',
                            'actions'=> array(
                                'add',
                                'attribute-details',
                                'delete',
                                'details',
                                'edit',
                                'index',
                                'save-attribute-order',
                            ),
                        ),
                        'debug' => array(
                            'name'=>'debug',
                            'actions' => array(
                                'index',
                                'toggle',
                            ),
                        ),
                        'emailqueue'=>array(
                            'name'=>'emailqueue',
                            'actions'=>array(
                                'delete',
                                'details',
                                'index',
                            ),
                        ),
                        'image'=>array(
                            'name'=>'image',
                            'actions'=>array(
                                'index',
                            ),
                        ),
                        'index'=>array(
                            'name'=>'index',
                            'actions'=>array(
                                'index',
                            ),
                        ),
                        'log'=>array(
                            'name'=>'log',
                            'actions'=>array(
                                'clear',
                                'index',
                            ),
                        ),
                        'login'=>array(
                            'name'=>'login',
                            'actions'=>array(
                                'forgot',
                                'index',
                                'logout',
                                'password-reset',
                                'signup',
                            ),
                        ),
                        'maintenance'=>array(
                            'name'=>'maintenance',
                            'actions'=>array(
                                'index',
                                'toggle',
                            ),
                        ),
                        'nav'=>array(
                            'name'=>'nav',
                            'actions'=>array(
                                'get-resources',
                                'index',
                                'save',
                            ),
                        ),
                        'oauth'=>array(
                            'name'=>'oauth',
                            'actions'=>array(
                                'add',
                                'all-consumers',
                                'delete',
                                'details',
                                'edit',
                                'generate-token',
                                'index',
                                'regenerate-consumer-keys',
                            ),
                        ),
                        'oauthclient'=>array(
                            'name'=>'oauthclient',
                            'actions'=>array(
                                'callback',
                                'index',
                            ),
                        ),
                        'oauthserver'=>array(
                            'name'=>'oauthserver',
                            'actions'=>array(
                                'access-token',
                                'already-authorized',
                                'authorize',
                                'deny',
                                'generate-token',
                                'grant',
                                'request-token',
                                'revoke',
                            ),
                        ),
                        'theme'=>array(
                            'name'=>'theme',
                            'actions'=>array(
                                'index',
                                'select',
                            ),
                        ),
                        'translate'=>array(
                            'name'=>'translate',
                            'actions'=>array(
                                'index',
                            ),
                        ),
                        'trigger'=>array(
                            'name'=>'trigger',
                            'actions'=>array(
                                'add',
                                'change-status',
                                'delete',
                                'details',
                                'edit',
                                'index',
                            ),
                        ),
                    ),
                ),
            ),
        );
        
        $this->assertEquals($data, $matchAgainst);
        
        $this->markTestIncomplete();
    }
    
    public function testSaveAction()
    {
        $this->markTestIncomplete();
    }
    
    public function testNavGetNav()
    {
        $this->markTestIncomplete();
    }
    
    public function testNavGenerateHtml()
    {
        
        $navData = array(
            'children' => array(
                array(
                    'show' => 1,
                    'display' => 'display',
                    'parent' => 'parent',
                    'id' => '5',
                    'module' => 'ot',
                    'controller' => 'nav',
                    'action' => 'index',
                    'link' => 'http://localhost/otframework/ot/nav/',
                    'allowed' => '1',
                    'target' => 'http://localhost/otframework/ot/nav/',
                    'children' => array()
                )
            )
        );
        $nav = new Ot_Nav();
        $html = '<li name="display" id="navItem_parent_5"><a href="http://localhost/otframework/ot/nav/" target="http://localhost/otframework/ot/nav/">display</a>' . "\n" . '</li>';
        $this->assertEquals($nav->generateHtml($navData), $html);
        
        
    }
    
    
    
    
}