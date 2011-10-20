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
 * @package    Oauth_Signature_Method_PLAINTEXT
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Signature_Method_PLAINTEXT
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Oauth_Signature_Method_PLAINTEXT extends Oauth_Signature_Method
{
    
    public function getName()
    {
        return "PLAINTEXT";
    }
    
    public function buildSignature($request, $consumer, $token)
    { 
        $sig = array (Oauth_Util::urlencodeRfc3986($consumer->secret));
        
        if ($token) {
            array_push($sig, Oauth_Util::urlencodeRfc3986($token->secret));
        } else {
            array_push($sig, '');
        }
        
        $raw = implode("&", $sig);
        // for debug purposes
        $request->baseString = $raw;
        
        return Oauth_Util::urlencodeRfc3986($raw);
    }
}