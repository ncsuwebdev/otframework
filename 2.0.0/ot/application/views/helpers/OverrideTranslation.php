<?php
class Zend_View_Helper_OverrideTranslation extends Zend_View_Helper_Translate
{
    /**
     * The baseUrl of the application
     *
     * @return unknown
     */
    protected $_baseUrl;
    
    public function overrideTranslation()
    {
        $this->_baseUrl = Zend_Layout::getMvcInstance()->getView()->baseUrl();    
    	return $this;
    }
   
    public function js()
    {
    	if ($this->_hasAccess()) {
    		echo '<script type="text/javascript" src="' . $this->_baseUrl . '/ot/scripts/translate.js"></script>';
    	}
    }
     
    public function link($text = 'Edit Text')
    {
    	$zcf = Zend_Controller_Front::getInstance();
    	
    	$request = $zcf->getRequest();

    	$url = $this->_baseUrl 
    	     . "/admin/translate/index"
    	     . "?module=" . $request->getModuleName() . "&"
    	     . "controller=" . $request->getControllerName() . "&"
    	     . "action=" . $request->getActionName()
    	     ;
    	     
    	if ($this->_hasAccess()) {
    		$translate = Zend_Registry::get('Zend_Translate');
    		echo '<div id="overrideTranslate"><a href="' . $url . '" id="locale_' . Ot_Language::getLanguageName($translate->getLocale()) . '">' . $text . '</a></div>';
    	}
    }
    
    protected function _hasAccess()
    {
    	$config = Zend_Registry::get('config');
    	$acl    = Zend_Registry::get('acl');
    	$auth   = Zend_Auth::getInstance();
    	
    	$role = (!$auth->hasIdentity()) ? (string)$config->user->defaultRole->val : $auth->getIdentity()->role;
        
    	return $acl->isAllowed($role, 'admin_translate', 'index');
    }
}