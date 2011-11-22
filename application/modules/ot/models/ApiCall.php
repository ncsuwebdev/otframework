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
 * @package    Ot_Api_Call
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to deal with method calls from the api
 * 
 * @package    Ot_Api_Call
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_ApiCall
{
    /**
     * available attributes for the call
     * 
     * @var array
     */
    protected $_attr = array(
       'class' => null,
       'method' => null,
    );
    
    /**
     * overide the set method to assign data to the attr array
     * 
     * @param $attr - attribute name
     * @param $value - value of the attribute
     */
    public function __set($attr, $value)
    {
        $this->_attr[$attr] = $value;
    }
    
    /**
     * override of the get method to retrieve data from the attr array
     * 
     * @param $attr - attribute name
     * @return value of attr
     */
    public function __get($attr)
    {
        return $this->_attr[$attr];
    }
    
    /**
     * constructor to create the api call method
     * 
     * @param class - class of the API call
     * @param method - method in the class to call
     */
    public function __construct($class, $method)
    {
        $this->class = $class;
        $this->method = $method;
    }
    
    /**
     * converts attr to array
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_attr;
    }
}