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
 * @package    Ot_FrontController_Plugin_ActiveUsers
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Handles the users' activity so that we can determine who is and isn't logged in
 *
 * @package    Ot_FrontController_Plugin_ActiveUsers
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_ActiveUsers extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
        
            $identity = Zend_Auth::getInstance()->getIdentity();

            $activeUser = new Ot_Model_DbTable_Activeuser();
            
            $thisUser = $activeUser->find($identity->accountId);
            
            $data = array(
                'accountId' => $identity->accountId,
                'dt'        => time()
            );
            
            try {
                               
                if (is_null($thisUser)) {
                    $activeUser->insert($data);   
                } else {
                    $activeUser->update($data, null);       
                }
                
                $activeUser->purgeInactiveUsers();
                
            } catch (Exception $e) {
                // we don't care if it fails. this shouldn't interrupt service at all
                return false;
            }
        }
    }
}