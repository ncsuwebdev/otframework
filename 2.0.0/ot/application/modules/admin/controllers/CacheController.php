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
 * @package    Admin_CacheController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to manage all application-wide configuration variables.
 *
 * @package    Admin_CacheController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_CacheController extends Zend_Controller_Action  
{   
    /**
     * Path to the config file
     *
     * @var string
     */
    protected $_configFilePath = '';
    
    /**
     * Setup flash messenger and the config file path
     *
     */
    public function init()
    {        
        $this->_configFilePath = Zend_Registry::get('configFilePath');
        
        parent::init();
    }
    
    /**
     * Shows the cache management index page
     */
    public function indexAction()
    {
        $messages = array();
                
        $this->view->acl = array(
            'clearCache' => $this->_helper->hasAccess('clear')
        );

        $this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $messages);
        $this->_helper->pageTitle('admin-cache-index:title');
    }
    
    /**
     * Clears the cache
     */
    public function clearAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $cache = Zend_Registry::get('cache');
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        
        $logOptions = array(
                'attributeName' => 'appConfig',
                'attributeId'   => '0',
        );
            
        $this->_helper->log(Zend_Log::INFO, 'Cache was cleared', $logOptions);
        
        $this->_helper->flashMessenger->addMessage('msg-info-cacheCleared');
        
        $this->_helper->redirector->gotoUrl('/admin/cache/');
    }
}