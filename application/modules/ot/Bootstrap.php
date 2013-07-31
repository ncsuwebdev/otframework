<?php
class Ot_Bootstrap extends Ot_Application_Module_Bootstrap
{
    protected function _initUrl()
    {
        $baseUrl = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/public/index.php'));

        $zcf = Zend_Controller_Front::getInstance();

        $zcf->setBaseUrl($baseUrl);

        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = substr(
            strtolower($_SERVER["SERVER_PROTOCOL"]),
            0,
            strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")
        ) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80" || $_SERVER['SERVER_PORT'] == '443') ? "" : (":" . $_SERVER["SERVER_PORT"]);
        $url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $baseUrl;

        Zend_Registry::set('siteUrl', $url);
    }

    /**
     * We must initialize and setup the session storage for the applications
     * authentication and authorization modules.
     *
     */
    public function _initAuth()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Ot_Auth_Storage_Session(Zend_Registry::get('siteUrl') . 'auth'));
    }

    public function _initPlugins()
    {
        $this->bootstrap('frontcontroller');

        $fc = Zend_Controller_Front::getInstance();

        $fc->registerPlugin(new Ot_FrontController_Plugin_Language());
        $fc->registerPlugin(new Ot_FrontController_Plugin_Input());
        $fc->registerPlugin(new Ot_FrontController_Plugin_Auth());
        $fc->registerPlugin(new Ot_FrontController_Plugin_Htmlheader());
        $fc->registerPlugin(new Ot_FrontController_Plugin_Nav());        
        $fc->registerPlugin(new Ot_FrontController_Plugin_MaintenanceMode());
        $fc->registerPlugin(new Ot_FrontController_Plugin_ActiveUsers());
    }

    public function _initThemes()
    {
        $this->bootstrap('vars');
        $this->bootstrap('registerThemes');

        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();

        $cr = new Ot_Config_Register();
        $tr = new Ot_Layout_ThemeRegister();                

        $theme = ($cr->getVar('theme')->getValue() != '') ? $cr->getVar('theme')->getValue() : 'default';
        
        $thisTheme = $tr->getTheme($theme);
        
        if (is_null($thisTheme)) {
            $thisTheme = $tr->getTheme('default');
        }
        
        $hr = new Ot_Layout_HeadRegister();
        
        foreach ($thisTheme->getCss() as $position => $cssFiles) {
            foreach ($cssFiles as $c) {
                $hr->registerCssFile($c, $position);
            }
        }
        
        foreach ($thisTheme->getJs() as $position => $jsFiles) {
            foreach ($jsFiles as $j) {
                $hr->registerJsFile($j, $position);
            }
        }
        
        $layout->setLayoutPath($thisTheme->getPath() . '/views/layouts');
        
        $view->addScriptPath(array($thisTheme->getPath() . '/views/scripts/'))
             ->addHelperPath(array($thisTheme->getPath() . '/views/helpers/'));
    }

    public function _initRoutes()
    {
        $this->bootstrap('frontcontroller');

        $router = Zend_Controller_Front::getInstance()->getRouter();

        $router->addRoute(
            'ot',
            new Zend_Controller_Router_Route(
                'ot/:controller/:action/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'index',
                    'action'     => 'index',
                )
            )
        )->addRoute(
            'login',
            new Zend_Controller_Router_Route(
                'login/:action/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'login',
                    'action'     => 'index',
                )
            )
        )->addRoute(
            'account',
            new Zend_Controller_Router_Route(
                'account/:action/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'account',
                    'action'     => 'index',
                )
            )
        )->addRoute(
            'api',
            new Zend_Controller_Router_Route(
                'api/:endpoint/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'api',
                    'action'     => 'index',
                    'endpoint'   => ''
                )
            )
        )->addRoute(
            'apiapp',
            new Zend_Controller_Router_Route(
                'apiapp/:action/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'apiapp',
                    'action'     => 'index',
                )
            )
        )->addRoute(
            'image',
            new Zend_Controller_Router_Route(
                'image/:action/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'image',
                    'action'     => 'index',
                )
            )
        )->addRoute(
            'cronjob',
            new Zend_Controller_Router_Route(
                'cronjob',
                array(
                    'module'     => 'ot',
                    'controller' => 'cronjob',
                    'action'     => 'index',
                )
            )
        );
    }

    public function _initTriggerActionTypes()
    {
        $plugins = array();

        $plugins[] = new Ot_Trigger_ActionType_EmailQueue('Ot_Trigger_ActionType_EmailQueue', 'Send email via Queue', 'Sends email using the built-in queue manager');
        $plugins[] = new Ot_Trigger_ActionType_Email('Ot_Trigger_ActionType_Email', 'Send email immediately', 'Send email immediately without queuing');
        
        $tpr = new Ot_Trigger_ActionTypeRegister();
        $tpr->registerTriggerActionTypes($plugins);
    }

    public function _initTriggers()
    {
        $forgotTrigger = new Ot_Trigger_Event('Login_Index_Forgot', 'Forgot Your Password', 'When a user has forgotten their password, they ask for a reset email to be sent to their registered email address.');
        $forgotTrigger->addOption('firstName', 'First name of the user')
                      ->addOption('lastName', 'Last name of the user.')
                      ->addOption('emailAddress', 'Email address of the user.')
                      ->addOption('username', 'Username of user.')
                      ->addOption('loginMethod', 'Name of login method which they use to log into the system with.')
                      ->addOption('resetUrl', 'URL the user will need to go to to reset their password.')
                      ;

        $signupTrigger = new Ot_Trigger_Event('Login_Index_Signup', 'Signup for a new account', 'When a user signs up for a new account.');
        $signupTrigger->addOption('firstName', 'First name of the user.')
                      ->addOption('lastName', 'Last name of the user.')
                      ->addOption('emailAddress', 'Email address of the user.')
                      ->addOption('username', 'Username of user.')
                      ->addOption('loginMethod', 'Name of login method which they use to log into the system with.')
                      ->addOption('password', 'The password they give to their account.')
                      ;

        $createPassword = new Ot_Trigger_Event('Admin_Account_Create_Password', 'Admin created an account with a password', 'When an administrator creates an account for a user where a password is dynamically generated for the user.');
        $createPassword->addOption('firstName', 'First name of the user.')
                       ->addOption('lastName', 'Last name of the user.')
                       ->addOption('emailAddress', 'Email address of the user.')
                       ->addOption('username', 'Username of user.')
                       ->addOption('loginMethod', 'Name of login method which they use to log into the system with.')
                       ->addOption('password', 'The password they give to their account.')
                       ->addOption('role', 'Assigned role given to the user.')
                       ;

        $noPassword = new Ot_Trigger_Event('Admin_Account_Create_NoPassword', 'Admin created an account with no password', 'When an administrator creates an account for a user when no password is created for the user.');
        $noPassword->addOption('firstName', 'First name of the user.')
                   ->addOption('lastName', 'Last name of the user.')
                   ->addOption('emailAddress', 'Email address of the user.')
                   ->addOption('username', 'Username of user.')
                   ->addOption('loginMethod', 'Name of login method which they use to log into the system with.')
                   ->addOption('role', 'Assigned role given to the user.')
                   ;

        $register = new Ot_Trigger_EventRegister();
        $register->registerTriggerEvents(array($forgotTrigger, $signupTrigger, $createPassword, $noPassword));
    }

    public function _initVars()
    {
        $site = array();

        $site[] = new Ot_Var_Type_Text('appTitle', 'Application Title', 'The title of the application.', 'OT Framework Application');
        $site[] = new Ot_Var_Type_Textarea('appDescription', 'Application Description', 'The application description.', 'App description!');
        $site[] = new Ot_Var_Type_Text('metaKeywords', 'Keywords', 'The meta keywords you would like to use for the application.', '');
        $site[] = new Ot_Var_Type_Theme('theme', 'Site Theme', 'The display theme for the application', 'default');
        $site[] = new Ot_Var_Type_Select('triggerSystem', 'Trigger System', 'Whether to globally enable or disable the trigger system. Enable in production.', '1', array(0 => 'Disabled', 1 => 'Enabled'));
        $site[] = new Ot_Var_Type_Select('showTrackbackOnError', 'Show Error Trackbacks', 'Switch to show error trackbacks when the application has errors.  Should likely be turned off in production.', '0', array(0 => 'No', 1 => 'Yes'));

        $auth = array();

        $auth[] = new Ot_Var_Type_Multiselect('requiredAccountFields', 'Required User Account Fields', 'When a user logs in, if these fields are not populated, they will be forced to populate the fields before continuing', array('firstName', 'lastName', 'emailAddress'), array('firstName' => 'First Name', 'lastName' => 'Last Name', 'emailAddress' => 'Email Address'));
        $auth[] = new Ot_Var_Type_Role('defaultRole', 'Default Role', 'Default role that a user gets if they are not logged in.', '1');
        $auth[] = new Ot_Var_Type_Role('newAccountRole', 'New Account Role', 'Role which is assigned to users when a new account is created for them', '1');

        $format = array();

        $format[] = new Ot_Var_Type_Text('dateTimeFormat', 'Date/Time Format', 'Date / Time formatted using PHP\'s strftime() function.', '%m/%d/%Y %I:%M %p');
        $format[] = new Ot_Var_Type_Text('medDateFormat', 'Medium Date Format', 'Date formatted using PHP\'s strftime() function.', '%b %e, %Y');
        $format[] = new Ot_Var_Type_Text('longDateCompactFormat', 'Long Date Compact Format', 'Date formatted using PHP\'s strftime() function.', '%a, %b %e, %Y');
        $format[] = new Ot_Var_Type_Text('longDateFormat', 'Long Date Format', 'Date formatted using PHP\'s strftime() function.', '%m/%d/%Y');
        $format[] = new Ot_Var_Type_Text('dayFormat', 'Day Format', 'Date formatted using PHP\'s strftime() function.', '%d');
        $format[] = new Ot_Var_Type_Text('timeFormat', 'Time Format', 'Time formatted using PHP\'s strftime() function.', '%I:%M %p');

        $vr = new Ot_Config_Register();
        $vr->registerVars($site, 'App Settings');
        $vr->registerVars($auth, 'Authentication');
        $vr->registerVars($format, 'Date/Time Formats');
    }

    public function _initCronjobs()
    {
        $cronjobs = array();
        
        $cronjobs[] = new Ot_Cron_Job('Ot_EmailQueue', 'Email Queue', 'Processes emails from the queue', '* * * * *', 'Ot_Cronjob_EmailQueue');
        
        $register = new Ot_Cron_JobRegister();
        $register->registerJobs($cronjobs);
        
    }

    public function _initApiMethods()
    {
        $register = new Ot_Api_Register();

        $endpoints = array();
        
        $endpoints[] = new Ot_Api_Endpoint('ot-account', 'Deals with the accounts in the system', 'Ot_Apiendpoint_Account');
        $endpoints[] = new Ot_Api_Endpoint('ot-version', 'Returns the OT Framework version numbers', 'Ot_Apiendpoint_Version');
        $endpoints[] = new Ot_Api_Endpoint('ot-cron', 'Deals with the cron jobs in the system', 'Ot_Apiendpoint_Cron');
        $endpoints[] = new Ot_Api_Endpoint('ot-myaccount', 'Deals with the current API account', 'Ot_Apiendpoint_MyAccount');
        
        $register->registerApiEndpoints($endpoints);
    }

    public function _initCustomFields()
    {
        // register types of vars available
        $varTypes = array();
        
        $varTypes[] = new Ot_CustomAttribute_FieldType('date', 'Date selector', 'Ot_Var_Type_Date');
        $varTypes[] = new Ot_CustomAttribute_FieldType('multiselect', 'Multi-Select Box', 'Ot_Var_Type_Multiselect', true);
        $varTypes[] = new Ot_CustomAttribute_FieldType('multicheckbox', 'Multi Checkbox', 'Ot_Var_Type_Multicheckbox', true);
        $varTypes[] = new Ot_CustomAttribute_FieldType('select', 'Dropdown Box', 'Ot_Var_Type_Select', true);
        $varTypes[] = new Ot_CustomAttribute_FieldType('text', 'Short Text Box', 'Ot_Var_Type_Text');
        $varTypes[] = new Ot_CustomAttribute_FieldType('textarea', 'Textarea', 'Ot_Var_Type_Textarea');
        $varTypes[] = new Ot_CustomAttribute_FieldType('checkbox', 'Checkbox', 'Ot_Var_Type_Checkbox');
        $varTypes[] = new Ot_CustomAttribute_FieldType('radio', 'Radio Buttons', 'Ot_Var_Type_Radio', true);
        $varTypes[] = new Ot_CustomAttribute_FieldType('description', 'Description', 'Ot_Var_Type_Description');
        $varTypes[] = new Ot_CustomAttribute_FieldType('ranking', 'Ranking', 'Ot_Var_Type_Ranking');
                
        $ftr = new Ot_CustomAttribute_FieldTypeRegister();
        $ftr->registerFieldTypes($varTypes);
        
        // Register host objects that these vars can be attached to
        $hosts = array();

        $hosts[] = new Ot_CustomAttribute_Host('Ot_Profile', 'User Account', 'Central OT Framework user account object');

        $cfor = new Ot_CustomAttribute_HostRegister();
        $cfor->registerHosts($hosts);
    }
    
    public function _initHead()
    {
        $this->bootstrap('registerThemes');
        
        $hr = new Ot_Layout_HeadRegister();
        
        $hr->registerCssFile('css/ot/common.css', 'prepend');        
        $hr->registerCssFile('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap.min.css', 'prepend');
        
        $hr->registerJsFile('scripts/ot/global.js', 'prepend');
        $hr->registerJsFile('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js', 'prepend');
        $hr->registerJsFile('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', 'prepend');
    }

    public function _initRegisterThemes()
    {        
        $tr = new Ot_Layout_ThemeRegister(); 
        
        $defaultTheme = new Ot_Layout_Theme('default', 'Default Theme', 'The default theme.', realpath(APPLICATION_PATH . '/../public/themes/ot/default'));                
        $defaultTheme->addCss('themes/ot/default/public/css/layout.css', 'prepend');
        
        $ncsuTheme = new Ot_Layout_Theme('ncsu', 'NC State Theme', 'Theme based on the NC State Homepage', realpath(APPLICATION_PATH . '/../public/themes/ot/ncsu'));        
                   
        $ncsuTheme->addCss('themes/ot/ncsu/public/css/layout.css', 'prepend');   
        $ncsuTheme->addCss('css/ncsubootstrap/css/ncsu-bootstrap.css', 'prepend');
        
        $ncsuTheme->addJs('themes/ot/ncsu/public/scripts/default.js', 'prepend'); 
        
        $tr->registerTheme($defaultTheme);
        $tr->registerTheme($ncsuTheme);        
    }
    
    public function _initAccountAttributeVars()
    {
        /**
         * This is an example of how to add an Account Var
         */
        
        /*
        $accountVars = array();

        $accountVars[] = new Ot_Var_Type_Text('dept', 'My Department', 'Your university department', 'PWD', array(), true);

        $aar = new Ot_Account_Attribute_Register();
        $aar->registerVars($accountVars);        
        */
    }
    
    public function _initAccountProfilePages()
    {
        /**
         * This is an example of how to add an account profile tab to the profile page
         */
        
        /*
        $accountPages = array();

        $accountPages[] = new Ot_Account_Profile_Page('test', 'This Is A Test', 'default', 'index', 'test', array('mytest' => 'value'));

        $apr = new Ot_Account_Profile_Register();
        $apr->registerPages($accountPages);        
        */
    }
}