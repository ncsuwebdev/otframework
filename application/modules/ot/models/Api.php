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
 * @package    Ot_Api
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles API functionality for the application.
 *
 * @package    Ot_Api
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * 
 */
class Ot_Model_Api
{
    /**
     * available functions that can be called remotely
     * 
     * @var array
     */
    protected $_functions = array();
    
    protected $_hasAccess = false;
    
    protected $_userId = '';
    
    /**
     * Constructor that populates the functions array from the api.xml file
     */
    public function __construct()
    {
        $configFiles = Zend_Registry::get('configFiles');
            
        $config = new Zend_Config_Xml($configFiles['api'], 'production');
            
        foreach ($config->apiCalls->call as $c) {
            $this->_functions[$c->apiName] = new Ot_Api_Call($c->class, $c->method);
        }                
    }
    
    /**
     * overwrite of the call method, that passes on the function call to the
     * proper API function as defined in the funcitons array
     * 
     * @param $function - Funciton name
     * @param $args     - array of arguments
     * @return result from the required function
     */
    public function __call($function, $args)
    {
    // Setup gcLogger
    $writer = new Zend_Log_Writer_Db(Zend_Registry::get('dbAdapter'), 'tbl_api_log');
    
    $logger = new Zend_Log($writer);
    $logger->setEventItem('timestamp', time());                                
    $logger->setEventItem('function', $function);        
    $logger->setEventItem('args', print_r($args, true));
    $logger->setEventItem('userId', $this->_userId);
    
        if (!isset($this->_functions[$function])) {
            return $this->_error('Function ' . $function . ' was not found');
        }
        
        if (!$this->_hasAccess) {
            return $this->_error('User does not have access');
        }
                        
        $call = $this->_functions[$function];                
        $obj = new $call->class($this->_userId);
        
        $result = call_user_func_array(array($obj, $call->method), $args);                
        if ($result === false) {
            return $this->_error(implode("\n", $obj->getMessages()));
        }
        
        try {
            $logger->info($function . ' was successful');
        } catch (Exception $e) {
            return $this->_error($e->getMessage());
        }
        
        return $result;
            
    }
    
    /**
     * Passes error messages back to the client
     * @param $msg - String
     * @return error string
     */
    protected function _error($msg)
    {
        return "*ERROR* : " . $msg;
    }
    
    /**
     * Shows all functions that are available for remote API calls.
     * 
     * @return array
     */
    public function __describe()
    {
        $ret = array();
        foreach ($this->_functions as $key => $value) {
            $ref = new ReflectionMethod($value->class, $value->method);
            
            $temp = array(
                'method'      => $key,
                'description' => $ref->getDocComment(),
                'args'        => array(),
            );
            
            $params = $ref->getParameters();
            foreach ($params as $p) {
                $temp['args'][] = $p->name;
            }
            
            $ret[] = $temp;
        }
        
        return $ret;
    }
    
    public function __setApiCode($accessCode)
    {
        $this->_hasAccess = false;
        $apiCode = new Ot_Api_Code();                    
        
        
        try {
            $apiCode->verify($accessCode);
        } catch (Exception $e) {
            return $this->_error($e->getMessage());
        }

        $this->_hasAccess = true;
        $this->_userId    = $apiCode->getApiUserId();  
        
        return true;
    }
}