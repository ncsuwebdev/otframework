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
 * @package    Ot_FrontController_Plugin_Nav
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Populates a navigation view variable based on the users credentials and the
 * nav layout as determined by the nav.xml config file
 *
 * @package    Ot_FrontController_Plugin_Nav
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_FrontController_Plugin_Nav extends Zend_Controller_Plugin_Abstract
{

    /**
     * Pre-dispatch code that builds the navigation array based on the access
     * level of the current user.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    { 
        $viewTabs = array();
        
        $nav        = Zend_Registry::get('navConfig');
        $sitePrefix = Zend_Registry::get('sitePrefix');        
        $acl        = Zend_Registry::get('acl');
        $config     = Zend_Registry::get('appConfig');
        
        $authz = Ot_Authz::getInstance();
        
        $role = ($authz->getRole() == '') ? (string)$config->loginOptions->defaultRole : $authz->getRole();
        
        $sitePrefix = Zend_Registry::get('sitePrefix');

        if ($nav->tabs->tab->get('0')) {
            foreach ($nav->tabs->tab as $main) {
                $tab = array(); 
            
                $tab = $this->_readNav($main, $role);
                
                if (!is_null($tab)) {
                    $viewTabs[] = $tab;
                }
            }
            
        } else {
            
            $tab = array(); 
            $tab = $this->_readNav($nav->tabs->tab, $role);

            if (!is_null($tab)) {
                $viewTabs[] = $tab;
            }
        }

        $vr = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $vr->view;
        $view->nav = $viewTabs;
    }
    
    private function _readNav($main, $role)
    {
        $sitePrefix = Zend_Registry::get('sitePrefix');  
        $acl        = Zend_Registry::get('acl');
        
        $tab = array();
            
        $tabRes = $main->module . '_' . (($main->controller == '') ? 'index' : $main->controller);

        $tab['display'] = $main->display;
        $tab['link']    = $this->_makeLink($sitePrefix, $main->module, $main->controller, $main->action, $main->link);
        $tab['target']  = (preg_match('/^http/i', $tab['link'])) ? '_blank' : '_self';
        $tab['sub']     = array();
        
        if ($main->submenu instanceof Zend_Config) {
            
            if ($main->submenu->tab->get('0')) {
                
                foreach ($main->submenu->tab as $sub) {
                    
                    $subTab = array();
                    
                    $subTabRes = $sub->module . '_' . (($sub->controller == '') ? 'index' : $sub->controller);

                    if ($acl->isAllowed($role, $subTabRes, ($sub->action == '') ? 'index' : $sub->action)) {
                        $subTab['display'] = $sub->display;
                        $subTab['link' ]   = $this->_makeLink($sitePrefix, $sub->module, $sub->controller, $sub->action, $sub->link);
                        $subTab['target'] = (preg_match('/^http/', $subTab['link'])) ? '_blank' : '_self';
                            
                        $tab['sub'][] = $subTab;
                    }
                }  
            } else {
                $sub = $main->submenu->tab;
                
                $subTab = array();
                        
                $subTabRes = $sub->module . '_' . (($sub->controller == '') ? 'index' : $sub->controller);
                        
                if ($acl->isAllowed($role, $subTabRes, ($sub->action == '') ? 'index' : $sub->action)) {
                    $subTab['display'] = $sub->display;
                    $subTab['link' ]   = $this->_makeLink($sitePrefix, $sub->module, $sub->controller, $sub->action, $sub->link);
                    $subTab['target'] = (preg_match('/^http/', $subTab['link'])) ? '_blank' : '_self';
                            
                    $tab['sub'][] = $subTab;
                }
            }
        }

        if (!$acl->isAllowed($role, $tabRes, ($main->action == '') ? 'index' : $main->action)) {
            if (count($tab['sub']) != 0) {
                $tab['link'] = '';
            } else {
                $tab = null;
            }
        }
        
        return $tab;
    }
    
    
    /**
     * Generates a link for the navigation menu
     *
     * @param string $sitePrefix
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $link
     * @return string
     */
    protected function _makeLink($sitePrefix, $module, $controller, $action, $link)
    {   	
        if ($link == '') {
            return $sitePrefix . '/' . 
                (($module != 'default') ? $module . '/' : '') . 
                (($controller != '') ? $controller . '/' : 'index/') . 
                (($action != '') ? $action . '/' : 'index/');
        }
        
        return (preg_match('/^http/', $link)) ? $link : $sitePrefix . ((preg_match('/^\//', $link)) ? '/' : '') . $link; 
    	
    }
}