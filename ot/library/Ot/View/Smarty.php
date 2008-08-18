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
 * @package    Ot_View_Smarty
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

// require the smarty library from http://smarty.php.net
require_once 'Smarty/Smarty.class.php';

/**
 * Allows Zend Framework's MVC architecture to use Smarty as the rendering engine
 *
 * @package    Ot_View_Smarty
 * @category   Access Control
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_View_Smarty extends Zend_View_Abstract
{
    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Initializes the smarty object, automatically called during abstract constructor
     * @return void
     */
    public function init()
    {
        $this->_smarty = new Smarty;
        
        $this->_smarty->caching = false;
        $this->_smarty->compile_dir  = './smarty/templates_c';
        $this->_smarty->cache_dir    = './smarty/cache';
        $this->_smarty->config_dir   = './smarty/configs';
        $this->_smarty->assign_by_ref('this', $this);
        $this->_smarty->compile_check = true;
    }
    
    /**
     * Return the template engine object
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }
    
    /**
     * Includes the view script in a scope with only public $this variables.
     *
     * @param string The view script to execute.
     */
    protected function _run() {
        $this->strictVars(true);
 
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ('_' != substr($key, 0, 1)) {
                $this->_smarty->assign($key, $value);
            }
        }
        //assign variables to the template engine
 
        $this->_smarty->register_object("this", $this, null, false);
        //why 'this'?
        //to emulate standard zend view functionality
        //doesn't mess up smarty in any way

        
        $paths = $this->getScriptPaths();

        foreach ($paths as $p) {
        	$file = substr(func_get_arg(0), strlen($p));
        	
        	if (is_file($p . $file)) {
		        $this->_smarty->template_dir = $p;
		        //set the template diretory as the first directory from the path
		 
		        echo $this->_smarty->fetch($file);
		        //process the template (and filter the output)     
		        return;   		
        	}
        }
        
        throw new Exception('Script path not found for ' . $file);
    }
}