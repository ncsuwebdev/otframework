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
class Admin_BackupController extends Zend_Controller_Action  
{         
    /**
     * Shows the backup index page
     */
    public function indexAction()
    {    	    	              
        $backup = new Ot_Backup();
        
        $form = $backup->_form();
                
        if ($this->_request->isPost()) {
        	
        	if ($form->isValid($_POST)) {
        		
        		$this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNeverRender();
	            
	            $db = Zend_Registry::get('dbAdapter');
	            $tableName = $_POST['tableName'];

	            // this call sends it to the browser too 
	            $backup->getCsv($db, $tableName);
	            
	            $logOptions = array(
                        'attributeName' => 'databaseTableBackup',
                        'attributeId'   => $tableName,
                );
                    
                $this->_helper->log(Zend_Log::INFO, 'Backup of database table ' . $tableName . ' was downloaded', $logOptions);
        	}
        }
        
        $this->view->form  = $form;
        $this->_helper->pageTitle('admin-backup-index:title');
    }
}