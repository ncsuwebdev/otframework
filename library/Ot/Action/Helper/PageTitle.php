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
 * @package    Ot_Action_Helper_PageTitle
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Adds additional features to a title of a page
 *
 * @package    Ot_Action_Helper_PageTitle
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Action_Helper_PageTitle extends Zend_Controller_Action_Helper_Abstract
{
    public function pageTitle($title, $options = array())
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->title = $view->translate($title, $options);
        
    }
    
    /**
     * Perform helper when called as $this->_helper->pageTitle() from an action controller
     *
     * @param  string $title
     * @param  array $options
     * @return string
     */
    public function direct($title, $options = array())
    {
        return $this->pageTitle($title, $options);
    }
}