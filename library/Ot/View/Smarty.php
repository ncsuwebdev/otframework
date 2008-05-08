<?php
require_once 'Smarty/Smarty.class.php';

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
 
        $path = $this->getScriptPaths();
 
        $file = substr(func_get_arg(0), strlen($path[0]));
        //smarty needs a template_dir, and can only use templates,
        //found in that directory, so we have to strip it from the filename
 
        $this->_smarty->template_dir = $path[0];
        //set the template diretory as the first directory from the path
 
        echo $this->_smarty->fetch($file);
        //process the template (and filter the output)
    }
}