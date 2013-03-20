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
 * @package    Ot_BackupController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the user to dump their database to a csv for backup purposes.
 *
 * @package    Ot_BackupController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_BackupController extends Zend_Controller_Action
{
    /**
     * Shows the backup index page
     */
    public function indexAction()
    {                                      
        $backup = new Ot_Model_Backup();
        
        $form = new Ot_Form_Backup(array('tables' => $backup->getTables()));
                
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNeverRender();
                
                // This call sends it to the browser too 
                $backup->getBackup($form->getValue('tableName'), $form->getValue('format'));
            }
        }
                
        $this->view->assign(array(
            'form'        => $form,
            'downloadAll' => ($this->_helper->hasAccess('download-all-sql') && !is_null(`mysqldump`)),
        ));
        
        $this->_helper->pageTitle('ot-backup-index:title');
    }
    
    public function downloadAllSqlAction()
    {
        if (!is_null(`mysqldump`)) {
            
            $backup = new Ot_Model_Backup();
               
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender();
                        
            // This call sends it to the browser too                 
            $backup->getBackup('', 'sqlAll');
                        
            $logOptions = array(
                'attributeName' => 'databaseTableBackup',
                'attributeId'   => 'allTables',
            );
                
            $this->_helper->log(Zend_Log::INFO, 'Backup of entire database was downloaded', $logOptions);
        } else {
            throw new Ot_Exception();
        }
    }
}