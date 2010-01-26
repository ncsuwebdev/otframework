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
 * @package    Ot_Action_Helper_Log
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Adds ability to log from the actions
 *
 * @package    Ot_Action_Helper_Log
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Action_Helper_Log extends Zend_Controller_Action_Helper_Abstract
{
    protected $_logger;
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        
        parent::init();
    }
    
    public function log($priority, $action, $options = array())
    {
        foreach ($options as $key => $value) {
            $this->_logger->setEventItem($key, $value);
        }
        
        $this->_logger->log($action, $priority);
    }

    public function direct($priority, $action, $options = array())
    {
        return $this->log($priority, $action, $options);
    }
}