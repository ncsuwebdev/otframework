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
 * @package    Oauth_Signature_Method_RSASHA1
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Signature_Method_RSASHA1
 * @category   OAuth
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Oauth_Signature_Method_RSASHA1 extends Oauth_Signature_Method
{
    public function getName()
    {
        return "RSA-SHA1";
    }
    
    protected function fetchPublicCert(&$request)
    {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        // (2) fetch via http using a url provided by the requester
        // (3) some sort of specific discovery code based on request
        //
        // either way should return a string representation of the certificate
        throw Exception("fetchPublicCert not implemented");
    }
    
    protected function fetchPrivateCert(&$request)
    {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        //
        // either way should return a string representation of the certificate
        throw Exception("fetchPrivateCert not implemented");
    }
    
    public function buildSignature(&$request, $consumer, $token)
    { 
        $baseString = $request->getSignatureBaseString();
        $request->baseString = $baseString;
        
        // Fetch the private key cert based on the request
        $cert = $this->fetchPrivateCert($request);
        
        // Pull the private key ID from the certificate
        $privatekeyid = opensslGetPrivatekey($cert);
        
        // Sign using the key
        $ok = openssl_sign($baseString, $signature, $privatekeyid);
        
        // Release the key resource
        openssl_free_key($privatekeyid);
        
        return base64_encode($signature);
    }
    
    public function checkSignature(&$request, $consumer, $token, $signature)
    {
        $decodedSig = base64_decode($signature);
        
        $baseString = $request->getSignatureBaseString();
        
        // Fetch the public key cert based on the request
        $cert = $this->fetchPublicCert($request);
        
        // Pull the public key ID from the certificate
        $publickeyid = openssl_get_publickey($cert);
        
        // Check the computed signature against the one passed in the query
        $ok = openssl_verify($baseString, $decodedSig, $publickeyid);
        
        // Release the key resource
        openssl_free_key($publickeyid);
        
        return $ok == 1;
    }
}