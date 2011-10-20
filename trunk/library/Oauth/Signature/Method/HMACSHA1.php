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
 * @package    Oauth_Signature_Method_HMACSHA1
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Signature_Method_HMACSHA1
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Oauth_Signature_Method_HMACSHA1 extends Oauth_Signature_Method
{
    
    public function getName()
    {
        return "HMAC-SHA1";
    }
    
    public function buildSignature($request, $consumer, $token)
    { 
        $baseString = $request->getSignatureBaseString();
        $request->baseString = $baseString;
        $keyParts = array ($consumer->secret, ($token) ? $token->secret : "" );
        $keyParts = Oauth_Util::urlencodeRfc3986($keyParts);
        $key = implode('&', $keyParts);
        
        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
    }
}