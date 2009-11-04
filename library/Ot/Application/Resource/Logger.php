<?php
class Ot_Application_Resource_Logger extends Zend_Application_Resource_ResourceAbstract
{   
    
    public function init()
    {
        $tbl = 'tbl_ot_log';
        
        $config = Zend_Registry::get('config');
        
        if (isset($config->app->tablePrefix) && !empty($config->app->tablePrefix)) {
            $tbl = $config->app->tablePrefix . $tbl;
        }
        
        // Setup logger
        $adapter = Zend_Db_Table::getDefaultAdapter();
        
        $writer = new Zend_Log_Writer_Db($adapter, $tbl);

        $logger = new Zend_Log($writer);

        $logger->addPriority('LOGIN', 8);

        $logger->setEventItem('sid', session_id());
        $logger->setEventItem('timestamp', time());
        $logger->setEventItem('request', str_replace(Zend_Controller_Front::getInstance()->getBaseUrl(), '', $_SERVER['REQUEST_URI']));

        $auth = Zend_Auth::getInstance();

        if (!is_null($auth->getIdentity())) {
            $logger->setEventItem('accountId', $auth->getIdentity()->accountId);
            $logger->setEventItem('role', $auth->getIdentity()->role);
        }

        Zend_Registry::set('logger', $logger);
    }
}