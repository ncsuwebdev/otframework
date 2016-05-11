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
 * @package    Ot_Auth_Adapter_Shib
 * @category   Authentication Adapter
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * This adapter users the Shib authentication mechanism that is provided on campus
 * webservers at NC State.  The default username and password passed to the constructor
 * are blank because WRAP handles the kerberos authentication to ensure the user is
 * an NCSU user.
 *
 * @package    Ot_Auth_Adapter_Wrap
 * @category   Authentication Adapter
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 */

use NCSU\Auth\AuthService,
    NCSU\Auth\Http\Request,
    NCSU\Auth\Adapter\ShibAuthAdapter;

class Ot_Auth_Adapter_Shib implements Zend_Auth_Adapter_Interface, Ot_Auth_Adapter_Interface
{
    /**
     * Authenticates the user passed by the constructor, however in this case we
     * user the Shib server variable "UNITY USERID" to get this appropriate username.
     *
     * @return new Zend_Auth_Result object
     */
    public function authenticate()
    {
        $request = Request::createFromGlobals();

        $shibAuthAdapter = new ShibAuthAdapter($request);
        $service = new AuthService($shibAuthAdapter);

        $result = $service->authenticate();

        if ($result->isValid()) {
            $class = new stdClass();
            $class->username = $result->getIdentity();
            $class->realm    = 'wrap';

            return new Zend_Auth_Result(true, $class, array());
        } else {
            echo "Failed to authenticate!";
        }
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
     * Logs the user out by removing all the Shib cookies that are created.
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
