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
 * @package    Oauth_Request
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * 
 *
 * @package    Oauth_Request
 * @category   Oauth_
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Oauth_Request
{
    private $_parameters;
    private $_httpMethod;
    private $_httpUrl;
    
    // for debug purposes
    public $baseString;
    public static $version = '1.0';
    
    function __construct($httpMethod, $httpUrl, $parameters = NULL)
    {
        
        @$parameters or $parameters = array();
        $this->_parameters = $parameters;
        $this->_httpMethod = $httpMethod;
        $this->_httpUrl    = $httpUrl;
    }
    
    
    /**
     * attempt to build up a request from what was passed to the server
     */
    public static function fromRequest($httpMethod = NULL, $httpUrl = NULL, $parameters = NULL)
    {
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") ? 'http' : 'https';
        @$httpUrl or $httpUrl = $scheme . '://' . $_SERVER['HTTP_HOST']
                              . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        @$httpMethod or $httpMethod = $_SERVER['REQUEST_METHOD'];
        
        $requestHeaders = Oauth_Request::getHeaders();
        
        /* Let the library user override things however they'd like, if they know
         * which parameters to use then go for it, for example XMLRPC might want to
         * do this.
         */
        if ($parameters) {
            $req = new Oauth_Request($httpMethod, $httpUrl, $parameters);
        } else {
            
            /* Collect request parameters from query string (GET) and post-data
             * (POST) if appropriate (note: POST vars have priority)
             */
            $reqParameters = $_GET;
            
            if ($httpMethod == "POST" && @strstr($requestHeaders["Content-Type"], "application/x-www-form-urlencoded")
            ) {
                $reqParameters = array_merge($reqParameters, $_POST);
            }
            
            /* Next check for the auth header, we need to do some extra stuff
             * if that is the case, namely suck in the parameters from GET or
             * POST so that we can include them in the signature.
             */
            if (@substr($requestHeaders['Authorization'], 0, 6) == "OAuth ") {
                $headerParameters = Oauth_Request::splitHeader($requestHeaders['Authorization']);
                $parameters = array_merge($reqParameters, $headerParameters);
                $req = new Oauth_Request($httpMethod, $httpUrl, $parameters);
            } else { 
                $req = new Oauth_Request($httpMethod, $httpUrl, $reqParameters);
            }
        }
        
        return $req;
    }
    
    /**
     * Pretty much a helper function to set up the request
     */
    public static function fromConsumerAndToken($consumer, $token, $httpMethod, $httpUrl, $parameters=NULL)
    {
        
        @$parameters or $parameters = array();
        $defaults = array(
            "oauth_version" => Oauth_Request::$version,
            "oauth_nonce" => Oauth_Request::generateNonce(),
            "oauth_timestamp" => Oauth_Request::generateTimestamp(),
            "oauth_consumer_key" => $consumer->key,
        );
        $parameters = array_merge($defaults, $parameters);
        
        if ($token) {
            $parameters['oauth_token'] = $token->key;
        }
        
        return new Oauth_Request($httpMethod, $httpUrl, $parameters);
    }
    
    public function setParameter($name, $value)
    {
        $this->_parameters[$name] = $value;
    }
    
    public function getParameter($name)
    {
        return isset($this->_parameters[$name]) ? $this->_parameters[$name] : null;
    }
    
    public function getParameters()
    {
        return $this->_parameters;
    }
    
    /**
     * Returns the normalized parameters of the request
     * 
     * This will be all (except Oauth__signature) parameters,
     * sorted first by key, and if duplicate keys, then by
     * value.
     *
     * The returned string will be all the key=value pairs
     * concated by &.
     * 
     * @return string
     */
    public function getSignableParameters()
    {
        // Grab all parameters
        $params = $this->_parameters;        
            
        // Remove Oauth__signature if present
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }
            
        // Urlencode both keys and values
        $keys = Oauth_Util::urlencodeRfc3986(array_keys($params));
        $values = Oauth_Util::urlencodeRfc3986(array_values($params));
        $params = array_combine($keys, $values);
        
        // Sort by keys (natsort)
        uksort($params, 'strcmp');
        
        // Generate key=value pairs
        $pairs = array();
        foreach ($params as $key =>$value ) {
            if (is_array($value)) {
                
                /* If the value is an array, it's because there are multiple 
                 * with the same key, sort them, then add all the pairs
                 */
                natsort($value);
                foreach ($value as $v2) {
                    $pairs[] = $key . '=' . $v2;
                }
            } else {
                $pairs[] = $key . '=' . $value;
            }
        }
            
        // Return the pairs, concated with &
        return implode('&', $pairs);
    }
    
    /**
     * Returns the base string of this request.
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function getSignatureBaseString()
    {
        $parts = array(
            $this->getNormalizedHttpMethod(),
            $this->getNormalizedHttpUrl(),
            $this->getSignableParameters()
        );
                
        $parts = Oauth_Util::urlencodeRfc3986($parts);
        
        return implode('&', $parts);
    }
    
    /**
     * just uppercases the http method
     */
    public function getNormalizedHttpMethod()
    {
        return strtoupper($this->_httpMethod);
    }
    
    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     */
    public function getNormalizedHttpUrl()
    {
        $parts = parse_url($this->_httpUrl);
        
        $port   = @$parts['port'];
        $scheme = $parts['scheme'];
        $host   = $parts['host'];
        $path   = @$parts['path'];
        
        $port or $port = ($scheme == 'https') ? '443' : '80';
        
        if (($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        
        return "$scheme://$host$path";
    }
    
    /**
     * builds a url usable for a GET request
     */
    public function toUrl()
    {
        $out  = $this->getNormalizedHttpUrl() . "?";
        $out .= $this->toPostdata();
        return $out;
    }
    
    /**
     * builds the data one would send in a POST request
     *
     * TODO(morten.fangel):
     * this function might be easily replaced with http_build_query()
     * and corrections for rfc3986 compatibility.. but not sure
     */
    public function toPostdata()
    {
        $total = array();
        foreach ($this->_parameters as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $va) {
                    $total[] = Oauth_Util::urlencodeRfc3986($k) . "[]=" . Oauth_Util::urlencodeRfc3986($va);
                }
            } else {
                $total[] = Oauth_Util::urlencodeRfc3986($k) . "=" . Oauth_Util::urlencodeRfc3986($v);
            }
        }
        $out = implode("&", $total);
        return $out;
    }
    
    /**
     * Builds the Authorization: header
     */
    public function toHeader()
    {
        $out ='Authorization: OAuth realm=""';

        foreach ($this->_parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth") { 
                continue;
            }
            
            if (is_array($v)) { 
                throw new Oauth_Exception('Arrays not supported in headers');
            }
            
            $out .= ',' . Oauth_Util::urlencodeRfc3986($k) . '="' . Oauth_Util::urlencodeRfc3986($v) . '"';
        }
        return $out;
    }
    
    public function __toString()
    {
        return $this->toUrl();
    }
    
    
    public function signRequest($signatureMethod, $consumer, $token)
    {
        $this->setParameter("oauth_signature_method", $signatureMethod->getName());
        $signature = $this->buildSignature($signatureMethod, $consumer, $token);
        $this->setParameter("oauth_signature", $signature);
    }
    
    public function buildSignature($signatureMethod, $consumer, $token)
    {
        $signature = $signatureMethod->buildSignature($this, $consumer, $token);
        return $signature;
    }
    
    /**
     * Util function: current timestamp
     */
    private static function generateTimestamp()
    {
        return time();
    }
    
    /**
     * Util function: current nonce
     */
    private static function generateNonce()
    {
        $mt   = microtime();
        $rand = mt_rand();
        
        return md5($mt . $rand); // md5s look nicer than numbers
    }
    
    /**
     * Util function for turning the Authorization: header into
     * parameters, has to do some unescaping
     */
    public static function splitHeader($header)
    {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = array();
        $matches = array();
        
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $headerName = $matches[2][0];
            $headerContent = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            $params[$headerName] = Oauth_Util::urldecodeRfc3986($headerContent);
            $offset = $match[1] + strlen($match[0]);
        }
        
        if (isset($params['realm'])) {
            unset($params['realm']);
        }
        
        return $params;
    }
    
    /**
     * helper to try to sort out headers for people who aren't running apache
     */
    public static function getHeaders()
    {
        if (function_exists('apache_request_headers')) {
            // we need this to get the actual Authorization: header
            // because apache tends to tell us it doesn't exist
            return apache_request_headers();
        }
        
        // otherwise we don't have apache and are just going to have to hope
        // that $_SERVER actually contains what we need
        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                // this is chaos, basically it is just there to capitalize the first
                // letter of every word that is not an initial HTTP and strip HTTP
                // code from przemek
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key] = $value;
            }
        }
        return $out;
    }      
}