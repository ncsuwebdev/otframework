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
 * @package    Admin_NavController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manages the navigational menus.
 *
 * @package    Admin_NavController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_NavController extends Zend_Controller_Action
{
    
    /**
     * The counter for the ids of the navigation elements.  This gets assigned
     * when writing the new nav structure to the config file.
     *
     * @var int
     */
    protected $_idCounter = 0;
    
    /**
     * A filter for making sure the navigation elements are correct
     */
    protected $_filter;
    
    /**
     * The Ot_Nav database table object so we don't have to create it
     * multiple times
     */
    protected $_otNav;
    
    /**
     * The ACL object for the application.  It's kept here because it's referenced
     * in a recursive method (processChildren()) and we don't need to get it
     * each time. 
     */
    protected $_acl;
    
    /**
     * 
     */
    public function init()
    {
        $this->_acl = Zend_Registry::get('acl');
        parent::init();
    }
    
    /**
     * Shows the editable nav structure.  This allows a user to add, edit, and 
     * delete, and reorder navigation elements.  Nothing is actually saveable
     * unless access to the Save action is granted.
     *
     */
    public function indexAction()
    {
        $this->acl = array(
                        'save' => $this->_helper->hasAccess('save')
                     );
        
        $this->_helper->pageTitle('admin-nav-index:title');;
        
        $this->view->siteUrl = Zend_Registry::get('siteUrl');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ot/scripts/jquery.plugin.jtree.js')
                                 ->appendFile($this->view->baseUrl() . '/ot/scripts/jquery.plugin.json.js');
                                 
        $nav = new Ot_Nav();
        $this->view->editNavTreeHtml = $nav->generateHtml(Zend_Registry::get('navArray'), true);
    }

    
    /**
     * Allows the user to get the resources available in the system to which 
     * access can be assigned.  This is the list of modules, controllers, and 
     * actions.  Access to this should be granted if the user has permission
     * to the index action.
     *
     */
    public function getResourcesAction()
    {
        $this->_helper->getStaticHelper('viewRenderer')->setNeverRender();
        $this->_helper->getStaticHelper('layout')->disableLayout();
        
        $aclResources = $this->_acl->getResources();
                
        $resources = array();
        
        foreach ($aclResources as $module => $controllers) {
            
            $mod = array();
            
            $mod['name'] = $module;
            foreach ($controllers as $controller => $controllerData) {
                $con = array();
                $con['name'] = $controller;
                
                foreach ($controllerData['part'] as $action => $actionData) {
                    $con['actions'][] = $action;
                }
                
                $mod['controllers'][$controller] = $con;
            }
            
            $resources['modules'][$module] = $mod;
        }
        
        echo Zend_Json::encode($resources);
    }

    /**
     * Allows the user to save the navigation structure to the database.
     *
     */
    public function saveAction()
    {
        $this->_helper->getStaticHelper('viewRenderer')->setNeverRender();
        $this->_helper->getStaticHelper('layout')->disableLayout();
        
        if ($this->_request->isPost()) {

            $rawData = Zend_Json_Decoder::decode($_POST['data']);
            
            $rawData = array(
                        'display'      => 'root',
                        'permissions'  => '',
                        'link'         => '',
                        'children'     => $rawData
                      );
            
            $this->_filter = new Zend_Filter();
            $this->_filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
            $this->_filter->addFilter(new Zend_Filter_StringToLower());
            
            $this->_otNav = new Ot_Nav();
                        
            // put all this stuff in a transaction to make sure nothing gets screwed up
            $this->_otNav->getAdapter()->beginTransaction();
            
            // empty the table
            $this->_otNav->delete(true);
            
            // adds ids and parent ids to the array as well as splits apart the link into module, controller, and action
            try {
                $this->_processChildren($rawData);
            } catch (Exception $e) {
                
                $this->_otNav->getAdapter()->rollBack();
                
                $retData = array('rc' => '0', 'msg' => $this->view->translate('msg-error-savingNav') . ' ' . $e->getMessage());
                echo Zend_Json_Encoder::encode($retData);
                return;
            }
    
            $this->_otNav->getAdapter()->commit();
            
            $cache = Zend_Registry::get('cache');
            $cache->remove('configObject');
            
            $logOptions = array(
                       'attributeName' => 'navigation',
                       'attributeId'   => 'modified',
            );
                    
            $this->_helper->log(Zend_Log::INFO, 'Navigation structure modified', $logOptions);
    
            $retData = array('rc' => '1', 'msg' => $this->view->translate('msg-info-savedNav'));
            echo Zend_Json_Encoder::encode($retData);
            return;
        }
    }
    
    /**
     * Recursively processes the array of navigation elements to insert them
     * into the database correctly
     *
     * @param array $a
     * @param int $parent
     */
    protected function _processChildren(&$a, $parent = 0)
    {
        $permissions     = explode(':', $a['permissions']);
        $a['module']     = (isset($permissions[0]) ? $permissions[0] : '');
        $a['controller'] = (isset($permissions[1]) ? $permissions[1] : '');
        $a['action']     = (isset($permissions[2]) ? $permissions[2] : '');
        
        $a['parent'] = $parent;
        $a['id']     = $this->_idCounter++;
       
        if ($a['id'] != 0) {
        
            if ($a['module'] == '') {
                $a['module'] = 'default';
            }
    
            if ($a['controller'] == '') {
                $a['controller'] = 'index';
            }
            
            try {
                $this->_acl->get($a['module'] . "_" . $a['controller']);
            } catch (Exception $e) {
                 throw new Exception($this->view->translate('msg-error-notValidResource', array($a['module'], $a['controller'])));
            }
            
            $tab = array(
                    'id'         => $a['id'],
                    'parent'     => $a['parent'],
                    'display'    => $a['display'],
                    'module'     => $this->_filter->filter($a['module']),
                    'controller' => $this->_filter->filter($a['controller']),
                    'action'     => $this->_filter->filter($a['action']),
                    'link'       => $a['link'],
                    'target'     => $a['target']
                   );
                   
            $this->_otNav->insert($tab);
        }
            
        foreach ($a['children'] as &$c) {
            $this->_processChildren($c, $a['id']);
        }
    }
}