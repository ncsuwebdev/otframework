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
 * @package    Ot_ActiveusersController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Shows the currently active (logged in users)
 *
 * @package    Ot_ActiveusersController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_ActiveusersController extends Zend_Controller_Action
{  
        
    /**
     * Shows the list of logged in users
     */
    public function indexAction()
    {
        $activeUser = new Ot_Model_DbTable_Activeuser();
        $otAccount = new Ot_Model_DbTable_Account();
        $otRole = new Ot_Model_DbTable_Role();
        
        $allActiveUsers = $activeUser->fetchAll(null, 'dt DESC')->toArray();
        
        foreach ($allActiveUsers as &$a) {
            $a['accountInfo'] = $otAccount->getByAccountId($a['accountId']);            
        }        
        
        $this->_helper->pageTitle('ot-activeusers-index:title');
        
        $this->view->assign(array(
            'activeUsers' => $allActiveUsers,
        ));
    }
}