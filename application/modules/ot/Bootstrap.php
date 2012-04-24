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
            'oauth',
            new Zend_Controller_Router_Route(
                'oauth/:action/*',
                array(
                    'module'     => 'ot',
                    'controller' => 'oauth',
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
        $vars = array();

        $vars[] = new Ot_Var('timezone', 'The current timezone for the application.', 'America/New_York');
        $vars[] = new Ot_Var('language', 'The default language for the application.', 'en');
        $vars[] = new Ot_Var('appTitle', 'The title of the application.', 'OT Framework Application');
        $vars[] = new Ot_Var('appDescription', 'The application description.', 'App description!');
        $vars[] = new Ot_Var('metaKeywords', 'The meta keywords you would like to use for the application.', '');
        $vars[] = new Ot_Var('fromEmailAddress', 'The global from email address.  All email from the system will come from this address.', 'admin@app.com');
        $vars[] = new Ot_Var('fromEmailName', 'The global from email name.  This is the name that will be shown with the fromEmailAddress.', 'Admin');
        $vars[] = new Ot_Var('fileUploadAllowableExtensions', 'The allowable extensions for files.  I would not suggest putting executable files in here...', 'pdf,doc,ppt,html,txt,zip');
        $vars[] = new Ot_Var('headerRowRepeat', 'The number of rows displayed in a table before the header cells are repeated.', '25');
        $vars[] = new Ot_Var('requiredAccountFields', 'When a user logs in, if these fields are not populated, they will be forced to populate the fields before continuing (separate by commas)', 'firstName, lastName, emailAddress');
        $vars[] = new Ot_Var('defaultRole', 'Default role that a user gets if they are not logged in.', '1');
        $vars[] = new Ot_Var('newAccountRole', 'Role which is assigned to users when a new account is created for them', '1');
        $vars[] = new Ot_Var('dateTimeFormat', 'Date / Time formatted using PHP\'s strftime() function.', '%m/%d/%Y %I:%M %p');
        $vars[] = new Ot_Var('medDateFormat', 'Date formatted using PHP\'s strftime() function.', '%b %e, %Y');
        $vars[] = new Ot_Var('longDateCompactFormat', 'Date formatted using PHP\'s strftime() function.', '%a, %b %e, %Y');
        $vars[] = new Ot_Var('timezone', 'Date formatted using PHP\'s strftime() function.', '%A, %B %e, %Y');
        $vars[] = new Ot_Var('longDateFormat', 'Date formatted using PHP\'s strftime() function.', '%m/%d/%Y');
        $vars[] = new Ot_Var('dayFormat', 'Date formatted using PHP\'s strftime() function.', '%d');
        $vars[] = new Ot_Var('timeFormat', 'Time formatted using PHP\'s strftime() function.', '%I:%M %p');
        $vars[] = new Ot_Var('minutesToKeepUserActivity', 'The number of minutes to keep the logged-in user activity.', '10');
        $vars[] = new Ot_Var('showTrackbackOnErrors', 'Whether or not to show the trackback of the error', '1');
        $vars[] = new Ot_Var('useMinify', 'Whether or not to use minify to combine and compress js, css, etc', '0');

        $vr = new Ot_Var_Register();
        $vr->registerVars($vars);
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
        $endpoint = new Ot_Api_Endpoint('Ot_Account', 'Deals with the accounts in the system');
        $endpoint->setMethod(new Ot_Model_Apiendpoint_Account());

        $register = new Ot_Api_Register();
        $register->registerApiEndpoint($endpoint);
    }
}