<?php
/**
 *
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
 * @package    Cron
 * @subpackage Cron_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Main cron controller
 *
 * @package    Cron_IndexController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 */
class Ot_CronjobController extends Zend_Controller_Action
{    
    /**
     * Initialization function
     *
     */
    public function init()
    {
        set_time_limit(0);
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();
        
        parent::init();
    }

    public function indexAction()
    {           
        $dispatcher = new Ot_Cron_Dispatcher();

        $dispatcher->dispatch();
    }
}