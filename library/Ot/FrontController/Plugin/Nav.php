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
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Populates a navigation view variable based on the users credentials and the
 * nav layout as determined by the nav.xml config file
 *
 * @package    Ot_FrontController_Plugin_Nav
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_Nav extends Zend_Controller_Plugin_Abstract
{
    
    protected $_treeNodes = array();

    /**
     * Pre-dispatch code that builds the navigation array based on the access
     * level of the current user.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $baseUrl = Zend_Layout::getMvcInstance()->getView()->baseUrl();  
        $acl     = Zend_Registry::get('acl');
         
        $viewTabs = array();

        $register = new Ot_Var_Register();

        $identity = Zend_Auth::getInstance()->getIdentity();
        
        $nav = new Ot_Model_DbTable_Nav();
        $tabs = $nav->getNav();
        
        $role = (empty($identity->role)) ? $register->defaultRole->getValue() : $identity->role;
                      
        foreach ($tabs as $tab) {
            
            $tabResource = $tab->module . '_' . (($tab->controller == '') ? 'index' : $tab->controller);
            
            $tabData = array();
    
            $tabData['display']    = $tab->display;
            $tabData['link']       = $this->_makeLink($baseUrl, $tab->link, $tab->target);
            $tabData['module']     = $tab->module;
            $tabData['controller'] = $tab->controller;
            $tabData['action']     = $tab->action;
            $tabData['target']     = (preg_match('/^http/i', $tabData['link'])) ? '_blank' : '_self';
            $tabData['parent']     = $tab->parent;
            $tabData['id']         = $tab->id;
            $tabData['allowed']    = $acl->isAllowed(
                $role, $tabResource,
                ($tab->action == '') ? 'index' : $tab->action
            );
            $tabData['show']       = $tabData['allowed'];
            
            $viewTabs[$tabData['id']] = $tabData;
        }
                
        $this->_treeNodes = $viewTabs;
        
        $navTree = array('id' => 0);
                        
        $navTree['children'] = $this->_buildTree($navTree);
           
        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();
        
        $view->navTreeHtml = $nav->generateHtml($navTree);
        Zend_Registry::set('navArray', $navTree);
    }
       
    protected function _buildTree($node)
    {
        $children = $this->_getChildren($node['id']);

        foreach ($children as $key => $child) {
            
            $kids = $this->_buildTree($child);
            
            $keepers = array();
            foreach ($kids as $k) {
                if ($k['show']) {
                    $children[$key]['show'] = true;
                    $keepers[] = $k;
                }
            }
            
            $children[$key]['children'] = $keepers;
        }
                    
        return $children;        
    }
    
    protected function _getChildren($parentId)
    {
        $children = array();
        
        foreach ($this->_treeNodes as $key => $n) {
            
            if ($n['id'] == $parentId) {
                
                unset($this->_treeNodes[$key]);
                
            } else if ($n['parent'] == $parentId) {
                $children[] = $n;
                
                unset($this->_treeNodes[$key]);
            }
        }
                
        return $children;
    }
    
    /**
     * Generates a link for the navigation menu
     *
     * @param string $baseUrl
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $link
     * @return string
     */
    protected function _makeLink($baseUrl, $link, $target)
    {   
        if ($link == '') {
            return '';    
        } elseif ($target == "_self") {
            return $baseUrl . '/' . $link;
        } else {
            return (preg_match('/^http/', $link))
                ? $link : $baseUrl . ((preg_match('/^\//', $link)) ? '/' : '') . $link;
        }
    }
}