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
class Ot_Auth_Adapter_Wrap implements Zend_Auth_Adapter_Interface, Ot_Auth_Adapter_Interface
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
    }

    /**
     * Authenticates the user passed by the constructor, however in this case we
     * user the WRAP server variable "WRAP_USERID" to get this appropriate username.
     *
     * @return new Zend_Auth_Result object
     */
    public function authenticate()
    {
        $username = (getenv('WRAP_USERID') == '') ? getenv('REDIRECT_WRAP_USERID') : getenv('WRAP_USERID');

        if ($username == '') {
            $username = getenv('REDIRECT_WRAP_USERID');
        }
        
        if ($username == '') {
            setrawcookie('WRAP_REFERER', $this->_getUrl(), 0, '/', '.ncsu.edu');
            header('location:https://webauth.ncsu.edu/wrap-bin/was16.cgi');
            die();
        }
        
        if (strtolower($username) == 'guest') {
            $this->autoLogout();
            return new Zend_Auth_Result(
               false,
               new stdClass(),
               array('Guest access is not allowed for this application')
            );
        }

        $class = new stdClass();
        $class->username = $username;
        $class->realm    = 'wrap';
        
        return new Zend_Auth_Result(true, $class, array());
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

        return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
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
        
        foreach (array_keys($_COOKIE) as $name) {
            if (preg_match('/^WRAP.*/', $name)) {

                // Set the expiration date to one hour ago
                setcookie($name, "", time() - 3600, "/", "ncsu.edu");
            }
        }
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

}