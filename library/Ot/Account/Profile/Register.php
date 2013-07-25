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
 * @package    Ot_Trigger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the email triggers
 *
 * @package    Ot_Trigger
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Account_Profile_Register
{
    const REGISTRY_KEY = 'Ot_Account_Profile_Register';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerPage(Ot_Account_Profile_Page $page)
    {
        $registered = $this->getPages();
        
        if (isset($registered[$page->getId()])) {
            throw new Ot_Exception('Account page ' . $page->getId() . ' already registered');
        }
        
        $registered[$page->getId()] = $page;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerPages(array $pages)
    {
        foreach ($pages as $p) {
            $this->registerPage($p);
        }
    }

    public function getPage($id)
    {
        $registered = $this->getPages();

        return (isset($registered[$id])) ? $registered[$id] : null;        
    }
    
    public function getPages()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);              
    }

    public function __get($id)
    {
        return $this->getPage($id);
    }
}

