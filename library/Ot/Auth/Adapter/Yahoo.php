<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Auth_Adapter_Wrap
 * @category   Authentication Adapter
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * This adapter users the WRAP authentication mechanism that is provided on campus
 * webservers at NC State.  The default username and password passed to the constructor
 * are blank because WRAP handles the kerberos authentication to ensure the user is
 * an NCSU user.
 *
 * @package    Ot_Auth_Adapter_Wrap
 * @category   Authentication Adapter
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Auth_Adapter_Yahoo implements Zend_Auth_Adapter_Interface, Ot_Auth_Adapter_Interface
{

    /**
     * Username of the user to authenticate
     *
     * @var string
     */
    protected $_username = '';

    /**
     * Password of the user to authenticate
     *
     * @var string
     */
    protected $_password = '';

    /**
     * Constant for default username for auto-login
     *
     */
    const defaultUsername = '';

    /**
     * Constant for default password for auto-login
     *
     */
    const defaultPassword = '';
    
    /**
     * Constructor to create new object
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username = self::defaultUsername, $password = self::defaultPassword)
    {
        $this->_username = $username;
        $this->_password = $password;
        
        if (Zend_Registry::isRegistered('applicationLoginOptions')) {
            $loginOptions = Zend_Registry::get('applicationLoginOptions');
            
            if (!defined('AUTH_YAHOO_CONSUMER_KEY')) {
                define('AUTH_YAHOO_CONSUMER_KEY', $loginOptions['adapteroptions']['yahoo']['consumerKey']);
            }
            
            if (!defined('AUTH_YAHOO_CONSUMER_SECRET')) {
                define('AUTH_YAHOO_CONSUMER_SECRET', $loginOptions['adapteroptions']['yahoo']['consumerSecret']);
            }           
        }
    }

    /**
     * Authenticates the user passed by the constructor, however in this case we
     * user the WRAP server variable "WRAP_USERID" to get this appropriate username.
     *
     * @return new Zend_Auth_Result object
     */
    public function authenticate()
    {        
        if (AUTH_YAHOO_CONSUMER_KEY == '' || AUTH_YAHOO_CONSUMER_SECRET == '') {
            throw new Exception('Yahoo authentication options must be set in the application configuration.');
        }
            
        $session = new Zend_Session_Namespace('ot_auth_adapter_yahoo');
        
        if (isset($session->authed)) {
            return new Zend_Auth_Result(true, unserialize($session->authed), array());
        }
        
        $config = array(
            'callbackUrl'     => $this->_getUrl() . Zend_Controller_Front::getInstance()->getBaseUrl() . '/login',
            'siteUrl'         => 'https://api.login.yahoo.com/oauth/v2/',
            'requestTokenUrl' => 'https://api.login.yahoo.com/oauth/v2/get_request_token',
            'authorizeUrl'    => 'https://api.login.yahoo.com/oauth/v2/request_auth',
            'accessTokenUrl'  => 'https://api.login.yahoo.com/oauth/v2/get_token',        
            'consumerKey'     => AUTH_YAHOO_CONSUMER_KEY,
            'consumerSecret'  => AUTH_YAHOO_CONSUMER_SECRET,
        );
        
        $consumer = new Zend_Oauth_Consumer($config);
        
        try {
            
            if (!isset($_GET['oauth_token']) && !$session->requestToken) {
                
                $token = $consumer->getRequestToken();
        
                $session->requestToken = serialize($token);
                
                $consumer->redirect();
                die();
                
            } else {
                $accessToken = $consumer->getAccessToken($_GET, unserialize($session->requestToken));

                unset($session->requestToken);
                
                parse_str($accessToken->getResponse()->getBody(), $body);

                $userId = $body['xoauth_yahoo_guid'];
            }
        } catch (Exception $e) {
            $session->unsetAll();
            
            return new Zend_Auth_Result(
               false,
               new stdClass(),
               array($e->getMessage())
            );
        }
        
        if (!isset($userId) || $userId == '') {
           return new Zend_Auth_Result(
               false,
               new stdClass(),
               array("Authentication Failed")
            );
        }        
        
        $class = new stdClass();
        $class->username = $userId;
        $class->realm    = 'yahoo';

        $session->authed = serialize($class);
        
        return new Zend_Auth_Result(true, $class, array());
    }

    /**
     * Setup this adapter to autoLogin
     *
     * @return boolean
     */
    public static function autoLogin()
    {
        return true;
    }

    /**
     * Logs the user out by removing all the WRAP cookies that are created.
     *
     */
    public static function autoLogout()
    {
        $session = new Zend_Session_Namespace('ot_auth_adapter_yahoo');
        $session->unsetAll();
        
    }

    /**
     * Flag to tell the app where the authenticaiton is managed
     *
     * @return boolean
     */
    public static function manageLocally()
    {
        return false;
    }
    
    /**
     * flag to tell the app whether a user can sign up or not
     *
     * @return boolean
     */
    public static function allowUserSignUp()
    {
        return false;
    }
    
    /**
     * Gets the current URL
     *
     * @return string
     */
    protected function _getURL()
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";

        $protocol = substr(
            strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")
        ) . $s;

        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);

        return $protocol."://".$_SERVER['SERVER_NAME'].$port;
    }    

}