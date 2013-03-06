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
 * @package    Ot_View_Helper_OverrideTranslation
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 * @package    Ot_View_Helper_OverrideTranslation
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_OverrideTranslation extends Zend_View_Helper_Translate
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
            echo '<script type="text/javascript" src="' . $this->_baseUrl . '/scripts/ot/translate.js"></script>';
        }
    }
     
    public function link($text = 'Edit Text')
    {
        $zcf = Zend_Controller_Front::getInstance();
        
        $request = $zcf->getRequest();

        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');
        
        $url = $helper->url(
            array(
                'controller' => 'translate',
                'm' => $request->getModuleName(),
                'c' => $request->getControllerName(),
                'a' => $request->getActionName(),
            ),
            'ot',
            true
        );
             
        if ($this->_hasAccess()) {
            $translate = Zend_Registry::get('Zend_Translate');
            echo '<div id="overrideTranslate"><a href="' . $url . '" id="locale_'
            . Ot_Model_Language::getLanguageName($translate->getLocale()) . '">' . $text . '</a></div>';
        }
    }
    
    protected function _hasAccess()
    {
        $registry = new Ot_Config_Register();

        $acl    = Zend_Registry::get('acl');
        $auth   = Zend_Auth::getInstance();
        
        $role = (!$auth->hasIdentity()) ? $registry->defaultRole->getValue() : $auth->getIdentity()->role;
        
        return $acl->isAllowed($role, 'ot_translate', 'index');
    }
}