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

require_once 'Facebook/facebook.php';

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
class Ot_Auth_Adapter_Facebook implements Zend_Auth_Adapter_Interface, Ot_Auth_Adapter_Interface
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
            
            if (!defined('AUTH_FB_APPID')) {
                define('AUTH_FB_APPID', $loginOptions['adapteroptions']['facebook']['appId']);
            }
            
            if (!defined('AUTH_FB_SECRET')) {
                define('AUTH_FB_SECRET', $loginOptions['adapteroptions']['facebook']['secret']);
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
        if (AUTH_FB_APPID == '' || AUTH_FB_SECRET == '') {
            throw new Exception('Yahoo authentication options must be set in the application configuration.');
        }
        
        // Create our Application instance (replace this with your appId and secret).
        $facebook = new Facebook(array(
          'appId'  => AUTH_FB_APPID,
          'secret' => AUTH_FB_SECRET,
          'cookie' => true,
        ));
        
        $session = $facebook->getSession();

        $username = null;

        // Session based API call.
        if ($session) {
            try {
                $uid = $facebook->getUser();
                $me = $facebook->api('/me');
            } catch (FacebookApiException $e) {
                return new Zend_Auth_Result(
                   false,
                   new stdClass(),
                   array($e->getMessage())
                );            
            }
        }

        // login
        if (!$me) {
            header('location:' . $facebook->getLoginUrl());
            die();
        }        
        
        $class = new stdClass();
        $class->username = $me['id'];
        $class->firstName = $me['first_name'];
        $class->lastName = $me['last_name'];
        $class->realm    = 'facebook';
        
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
    {}

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

}