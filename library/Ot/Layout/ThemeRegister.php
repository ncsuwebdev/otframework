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
class Ot_Layout_ThemeRegister
{
    const REGISTRY_KEY = 'Ot_Theme_Register';

    public function __construct()
    {
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, array());
        }
    }

    public function registerTheme(Ot_Layout_Theme $theme)
    {
        $registered = $this->getThemes();
        
        if (isset($registered[$theme->getName()])) {
            throw new Ot_Exception('Theme ' . $theme->getName() . ' already registered');
        }
        
        $registered[$theme->getName()] = $theme;

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function registerThemes(array $themes)
    {
        foreach ($themes as $t) {
            $this->registerTheme($t);
        }
    }

    public function getTheme($name)
    {
        $registered = $this->getThemes();

        return (isset($registered[$name])) ? $registered[$name] : null;        
    }
    
    public function getThemes()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);                      
    }

    public function __get($name)
    {
        return $this->getTheme($name);
    }
}

