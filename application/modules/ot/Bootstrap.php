<?php
class Ot_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
    }

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
        $fc->registerPlugin(new Ot_FrontController_Plugin_DebugMode());
        $fc->registerPlugin(new Ot_FrontController_Plugin_MaintenanceMode());
        $fc->registerPlugin(new Ot_FrontController_Plugin_ActiveUsers());
    }

    public function _initTheme()
    {
        $this->bootstrap('vars');

        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();

        $registry = new Ot_Var_Register();

        $theme = ($registry->theme->getValue() != '') ? $registry->theme->getValue() : 'default';

        $appBasePath = APPLICATION_PATH . "/../public/themes";
        $otBasePath = APPLICATION_PATH . "/../public/themes/ot";

        if (!is_dir($appBasePath . "/" . $theme)) {

            if (!is_dir($otBasePath . '/' . $theme)) {
                $theme = 'default';
            }

            $themePath = $otBasePath . '/' . $theme;

        } else {
            $themePath = $appBasePath . '/' . $theme;
        }

        $layout->setLayoutPath($themePath . '/views/layouts');

        $view->applicationThemePath = str_replace(APPLICATION_PATH . "/../public/", "", $themePath);

        $view->addScriptPath(array($themePath . '/views/scripts/'))
             ->addHelperPath(array($themePath . '/views/helpers/'));

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

    public function _initTriggerPlugins()
    {
        $plugins = array();

        $plugins[] = new Ot_TriggerPlugin('Ot_Trigger_Plugin_EmailQueue', 'Send email via Queue');
        $plugins[] = new Ot_TriggerPlugin('Ot_Trigger_Plugin_Email', 'Send email');
        //$plugins[] = new Ot_TriggerPlugin('Ot_Trigger_Plugin_Txt', 'Send text message');
        //$plugins[] = new Ot_TriggerPlugin('Ot_Trigger_Plugin_TxtQueue', 'Send text message via Queue');

        $tpr = new Ot_Trigger_PluginRegister();
        $tpr->registerTriggerPlugins($plugins);
    }

    public function _initTriggers()
    {
        $forgotTrigger = new Ot_Trigger('Login_Index_Forgot', 'When a user has forgotten their password, they ask for a reset email to be sent to their registered email address.');
        $forgotTrigger->addOption("firstName", "First name of the user")
                      ->addOption("lastName", "Last name of the user.")
                      ->addOption("emailAddress", "Email address of the user.")
                      ->addOption("username", "Username of user.")
                      ->addOption("loginMethod", "Name of login method which they use to log into the system with.")
                      ->addOption("resetUrl", "URL the user will need to go to to reset their password.")
                      ;

        $signupTrigger = new Ot_Trigger('Login_Index_Signup', "When a user signs up for a new account.");
        $signupTrigger->addOption("firstName", "First name of the user.")
                      ->addOption("lastName", "Last name of the user.")
                      ->addOption("emailAddress", "Email address of the user.")
                      ->addOption("username", "Username of user.")
                      ->addOption("loginMethod", "Name of login method which they use to log into the system with.")
                      ->addOption("password", "The password they give to their account.")
                      ;

        $createPassword = new Ot_Trigger("Admin_Account_Create_Password", "When an administrator creates an account for a user where a password is dynamically generated for the user.");
        $createPassword->addOption("firstName", "First name of the user.")
                       ->addOption("lastName", "Last name of the user.")
                       ->addOption("emailAddress", "Email address of the user.")
                       ->addOption("username", "Username of user.")
                       ->addOption("loginMethod", "Name of login method which they use to log into the system with.")
                       ->addOption("password", "The password they give to their account.")
                       ->addOption("role", "Assigned role given to the user.")
                       ;

        $noPassword = new Ot_Trigger("Admin_Account_Create_NoPassword", "When an administrator creates an account for a user when no password is created for the user.");
        $noPassword->addOption("firstName", "First name of the user.")
                   ->addOption("lastName", "Last name of the user.")
                   ->addOption("emailAddress", "Email address of the user.")
                   ->addOption("username", "Username of user.")
                   ->addOption("loginMethod", "Name of login method which they use to log into the system with.")
                   ->addOption("role", "Assigned role given to the user.")
                   ;

        $register = new Ot_Trigger_Register();
        $register->registerTriggers(array($forgotTrigger, $signupTrigger, $createPassword, $noPassword));
    }

    public function _initVars()
    {
        $site = array();

        $site[] = new Ot_Var_Type_Text('appTitle', 'Application Title', 'The title of the application.', 'OT Framework Application');
        $site[] = new Ot_Var_Type_Textarea('appDescription', 'Application Description', 'The application description.', 'App description!');
        $site[] = new Ot_Var_Type_Text('metaKeywords', 'Keywords', 'The meta keywords you would like to use for the application.', '');
        $site[] = new Ot_Var_Type_Select('useMinify', 'Use Minify', 'Whether or not to use minify to combine and compress js, css, etc', '0', array(0 => 'No', 1 => 'Yes'));
        $site[] = new Ot_Var_Type_Theme('theme', 'Site Theme', 'The display theme for the application', 'default');
        
        $auth = array();

        $auth[] = new Ot_Var_Type_MultiSelect('requiredAccountFields', 'Required User Account Fields', 'When a user logs in, if these fields are not populated, they will be forced to populate the fields before continuing', array('firstName', 'lastName', 'emailAddress'), array('firstName' => 'First Name', 'lastName' => 'Last Name', 'emailAddress' => 'Email Address'));
        $auth[] = new Ot_Var_Type_Role('defaultRole', 'Default Role', 'Default role that a user gets if they are not logged in.', '1');
        $auth[] = new Ot_Var_Type_Role('newAccountRole', 'New Account Role', 'Role which is assigned to users when a new account is created for them', '1');
        
        $format = array();

        $format[] = new Ot_Var_Type_Text('dateTimeFormat', 'Date/Time Format', 'Date / Time formatted using PHP\'s strftime() function.', '%m/%d/%Y %I:%M %p');
        $format[] = new Ot_Var_Type_Text('medDateFormat', 'Medium Date Format', 'Date formatted using PHP\'s strftime() function.', '%b %e, %Y');
        $format[] = new Ot_Var_Type_Text('longDateCompactFormat', 'Long Date Compact Format', 'Date formatted using PHP\'s strftime() function.', '%a, %b %e, %Y');
        $format[] = new Ot_Var_Type_Text('longDateFormat', 'Long Date Format', 'Date formatted using PHP\'s strftime() function.', '%m/%d/%Y');
        $format[] = new Ot_Var_Type_Text('dayFormat', 'Day Format', 'Date formatted using PHP\'s strftime() function.', '%d');
        $format[] = new Ot_Var_Type_Text('timeFormat', 'Time Format', 'Time formatted using PHP\'s strftime() function.', '%I:%M %p');
        
        $vr = new Ot_Var_Register();
        $vr->registerVars($site, 'Site Settings');
        $vr->registerVars($auth, 'Authentication');
        $vr->registerVars($format, 'Date/Time Formats');
    }

    public function _initCronjobs()
    {
        $eq = new Ot_Cron('Ot_EmailQueue', 'Processes emails from the queue', '* * * * *');
        $eq->setMethod(new Ot_Model_Cronjob_EmailQueue());

        $register = new Ot_Cron_Register();
        $register->registerCronjob($eq);
    }
    
    public function _initApiMethods()
    {
        $register = new Ot_Api_Register();
        
        $endpoint = new Ot_Api_Endpoint('Ot_Account', 'Deals with the accounts in the system');
        $endpoint->setMethod(new Ot_Model_Apiendpoint_Account());
        $register->registerApiEndpoint($endpoint);
        
        $endpoint = new Ot_Api_Endpoint('Ot_Version', 'Returns the OT Framework version numbers');
        $endpoint->setMethod(new Ot_Model_Apiendpoint_Version());
        $register->registerApiEndpoint($endpoint);
        
        $endpoint = new Ot_Api_Endpoint('Ot_Cron', 'Deals with the cron jobs in the system');
        $endpoint->setMethod(new Ot_Model_Apiendpoint_Cron());
        $register->registerApiEndpoint($endpoint);
        
        $endpoint = new Ot_Api_Endpoint('Ot_MyAccount', 'Deals with the current API account');
        $endpoint->setMethod(new Ot_Model_Apiendpoint_MyAccount());
        $register->registerApiEndpoint($endpoint);
        
        $endpoint = new Ot_Api_Endpoint('Ot_Bug', 'Deal with bug reports');
        $endpoint->setMethod(new Ot_Model_Apiendpoint_Bug());
        $register->registerApiEndpoint($endpoint);
    }

    public function _initCustomFieldObjects()
    {
        $objects = array();

        $objects[] = new Ot_CustomFieldObject('Ot_Profile', 'User Profile');

        $cfor = new Ot_CustomFieldObject_Register();
        $cfor->registerCustomFieldObjects($objects);
    }
}