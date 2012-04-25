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
 * @package    Ot_Application_Resource_Logger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 *
 * @package   Ot_Application_Resource_Logger
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */

class Ot_Application_Resource_Logger extends Zend_Application_Resource_ResourceAbstract
{
    protected $_useLog = false;
    
    public function setUseLog($val)
    {
        $this->_useLog = (bool)$val;
    }
    
    public function init()
    {
        $this->getBootstrap()->bootstrap(array('db'));
        
        $tbl = 'tbl_ot_log';

        $application = $this->getBootstrap()->getApplication();
        
        $prefix = $application->getOption('tablePrefix');

        if (!empty($prefix)) {
            $tbl = $prefix . $tbl;
        }
                
        // Setup logger
        if ($this->_useLog) {
            $adapter = Zend_Db_Table::getDefaultAdapter();
            $writer  = new Zend_Log_Writer_Db($adapter, $tbl);
        } else {
            $writer = new Zend_Log_Writer_Null();
        }

        $logger = new Zend_Log($writer);

        $logger->addPriority('LOGIN', 8);

        $logger->setEventItem('sid', session_id());
        $logger->setEventItem('timestamp', time());
        $logger->setEventItem(
            'request',
            str_replace(Zend_Controller_Front::getInstance()->getBaseUrl(), '', $_SERVER['REQUEST_URI'])
        );

        Zend_Registry::set('logger', $logger);
    }
}