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
class Ot_Layout_HeadRegister
{
    const REGISTRY_KEY = 'Ot_Head_Register';

    public function __construct()
    {
        $emptyRegistry = array(
            'css' => array(
                'prepend' => array(),
                'append'  => array(),
            ),
            'js' => array(
                'prepend' => array(),
                'append'  => array(),
            ),
        );
        
        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            Zend_Registry::set(self::REGISTRY_KEY, $emptyRegistry);
        }
    }
    
    public function registerJsFile($file, $position = 'append')
    {
        return $this->_registerHeadFile('js', $file, $position);
    }
    
    public function registerCssFile($file, $position = 'append')
    {
        return $this->_registerHeadFile('css', $file, $position);
    }

    protected function _registerHeadFile($type, $file, $position = 'append')
    {
        $registered = $this->_getHeadFiles();                
        
        if (isset($registered[$type][$position])) {
            $registered[$type][$position][] = $file;
        }        

        Zend_Registry::set(self::REGISTRY_KEY, $registered);
    }

    public function getCssFiles()
    {
        $head = $this->_getHeadFiles();                 
        
        return $head['css'];
    }
    
    public function getJsFiles()
    {
        $head = $this->_getHeadFiles();                 
        
        return $head['js'];
    }

    protected function _getHeadFiles()
    {
        return Zend_Registry::get(self::REGISTRY_KEY);
    }

    public function __get($name)
    {
        return $this->getTheme($name);
    }
}

