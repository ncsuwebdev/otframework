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
 * @package    Admin_BackupController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to dump their database to a csv for backup purposes.
 *
 * @package    Admin_BackupController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_BackupController extends Internal_Controller_Action  
{       
    /**
     * Flash messenger variable
     *
     * @var unknown_type
     */
    protected $_flashMessenger = null;
    
    /**
     * Setup flash messenger and the config file path
     *
     */
    public function init()
    {
        
        $this->_flashMessenger = $this->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('backup');
        
        parent::init();
    }
    
    /**
     * Shows the backup index page
     */
    public function indexAction()
    {    	    	        
        $db = Zend_Registry::get('dbAdapter');
        
        $tables = $db->listTables();
        
        $tableList = array();
        
        foreach ($tables as $t) {
        	$tableList[$t] = $t;
        }
                
        $this->view->tables = $tableList;
        
        $this->view->title = 'Backup Admin';
    }
    
    /**
     * Retrieves the backup
     */
    public function getBackupAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNeverRender();
    	
    	if ($this->_request->isPost()) {
    		$backup = new Ot_Backup();
    		
    		$db = Zend_Registry::get('dbAdapter');
    		$tableName = $_POST['tableName'];
    		
    		$backup->getCsv($db, $tableName);
    	}
    }
}