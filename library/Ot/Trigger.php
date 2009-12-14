<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Trigger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the email triggers
 *
 * @package    Ot_Trigger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Trigger
{    
    /**
     * The variables to be replaced in the email
     *
     * @var unknown_type
     */
    protected $_vars = array();
    
       
    /**
     * Overrides the set method so that we can wrap the variables for the email
     * in a nice package. 
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function __set($name, $value)
    {
        $this->_vars[$name] = $value;        
    }
    
    /**
     * Sets an array of email variables
     *
     * @param array $data
     */
    public function setVariables(array $data)
    {
    	$this->_vars = array_merge($this->_vars, $data);
    }
    
    /**
     * Dispatches the trigger specified 
     * 
     * @param int $triggerId
     */
    public function dispatch($triggerId)
    {
    	$action = new Ot_Trigger_Action();
    	$actions = $action->getActionsForTrigger($triggerId);
    	
        foreach ($actions as $a) {
        	$helper = new $a->helper;
        	
        	$data = $helper->get($a->triggerActionId);
        	
        	foreach ($data as &$d) {
        		foreach ($this->_vars as $key => $value) {
        			$d = str_replace("[[$key]]", $value, $d);
        		}
        	}
        	
        	$helper->dispatch($data);
        }
    }
}

