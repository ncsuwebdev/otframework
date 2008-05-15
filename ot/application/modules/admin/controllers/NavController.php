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
 * @package    Ot Admin
 * @subpackage Admin_NavController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: LogController.php 210 2007-08-01 18:23:50Z jfaustin@EOS.NCSU.EDU $
 */
class Admin_NavController extends Internal_Controller_Action 
{
     /**
     * shows the homepage
     *
     */
    public function indexAction()
    {        
        $this->view->title = 'Navigation Editor';
        
        $nav = new Zend_Config_Xml('./config/nav.xml', 'production');
        
        $navData = array();
        
        if (isset($nav->tabs->tab->{0})) {
            foreach ($nav->tabs->tab as $t) {
                $navData['tabs']['tab'][] = $this->_getNavInfo($t);
            }            
        } else {
            $navData['tabs']['tab'][] = $this->_getNavInfo($nav->tabs->tab);
        }
       
        $this->view->navData = $navData;
    }
    
    private function _getNavInfo($t)
    {
        $tmp = array();
        $tmp['module'] = $t->module;
        $tmp['controller'] = $t->controller;
        $tmp['action'] = $t->action;
        $tmp['display'] = htmlentities($t->display, ENT_QUOTES);
        $tmp['link'] = $t->link;
        $tmp['info'] = Zend_Json_Encoder::encode($tmp);
            
        if (isset($t->submenu->tab)) {
            
            if (isset($t->submenu->tab->{0})) {                   
                
                foreach ($t->submenu->tab as $subt) {
                    $tmpSub = array();
                    $tmpSub['module'] = $subt->module;
                    $tmpSub['controller'] = $subt->controller;
                    $tmpSub['action'] = $subt->action;
                    $tmpSub['display'] = htmlentities($subt->display, ENT_QUOTES);
                    $tmpSub['link'] = $subt->link;
                    $tmpSub['info'] = Zend_Json_Encoder::encode($tmpSub);
                    $tmp['submenu']['tab'][] = $tmpSub;
                }
                
            } else {
                
                $tmpSub = array();
                $tmpSub['module'] = $t->submenu->tab->module;
                $tmpSub['controller'] = $t->submenu->tab->controller;
                $tmpSub['action'] = $t->submenu->tab->action;
                $tmpSub['display'] = htmlentities($t->submenu->tab->display, ENT_QUOTES);
                $tmpSub['link'] = $t->submenu->tab->link;
                $tmpSub['info'] = Zend_Json_Encoder::encode($tmpSub);
                $tmp['submenu']['tab'][] = $tmpSub;
                
            }
        }
        
        return $tmp;
    }
    
    public function saveAction()
    {
        $this->_helper->getStaticHelper('viewRenderer')->setNoRender();
        $this->_helper->getStaticHelper('layout')->disableLayout();
        
        if ($this->_request->isPost()) {
            
            $rawData = Zend_Json_Decoder::decode($_POST['data']);            

            $data = array();
            foreach ($rawData as $r) {
                $tmp = array();
                $tmp = Zend_Json_Decoder::decode($r['title']);

                if ($r['children']) {
                    foreach ($r['children'] as $child) {
                        $tmp['submenu'][] = Zend_Json_Decoder::decode($child['title']);
                    }
                }
                $data[] = $tmp;
            }           
            
            $filePath = './config/nav.xml';
            
            if (file_exists($filePath)) {
                
                $xml = simplexml_load_file($filePath);
             
            } else {
                $retData = array('rc' => '0', 'msg' => 'Error loading navigation xml file');
                echo Zend_Json_Encoder::encode($retData);
                return;
            }
            
            if (!is_writable($filePath)) {
                $retData = array('rc' => '0', 'msg' => 'Navigation xml file is not writable');
                echo Zend_Json_Encoder::encode($retData);
                return;
            }
            
            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
            $filter->addFilter(new Zend_Filter_StringToLower());
            
            foreach ($data as &$t) {
                
                if ($t['module'] == "") {
                    $t['module'] = "default";
                }
                
                if ($t['controller'] == "") {
                    $t['controller'] = "index";
                }
                
                $t['module'] = $filter->filter($t['module']);
                $t['controller'] = $filter->filter($t['controller']);
                $t['action'] = $filter->filter($t['action']);
                
                try {
                    $this->_acl->get($t['module'] . "_" . $t['controller']);
                } catch (Exception $e) {
                     $retData = array('rc' => '0', 'msg' => "Save Failed! " . $t['module'] . "_" . $t['controller'] . " is not a valid resource");
                     echo Zend_Json_Encoder::encode($retData);
                     return;               
                }
            }
            
            $doc = new DOMDocument('1.0');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
    
            $conf = $doc->createElement('configdata');
            $conf = $doc->appendChild($conf);
            
            $prod = $doc->createElement('production');
            $prod = $conf->appendChild($prod);
            
            $root = $doc->createElement('tabs');
            $root = $prod->appendChild($root);
            
            foreach ($data as &$t) {
                
                $tab = $doc->createElement('tab');
                $tab = $root->appendChild($tab);
    
                $module = $doc->createElement('module');
                $module = $tab->appendChild($module);
    
                $moduleValue = $doc->createTextNode($t['module']);
                $moduleValue = $module->appendChild($moduleValue);
    
                $controller = $doc->createElement('controller');
                $controller = $tab->appendChild($controller);
    
                $controllerValue = $doc->createTextNode($t['controller']);
                $controllerValue = $controller->appendChild($controllerValue);
                
                $action = $doc->createElement('action');
                $action = $tab->appendChild($action);
    
                $actionValue = $doc->createTextNode($t['action']);
                $actionValue = $action->appendChild($actionValue);
                
                $display = $doc->createElement('display');
                $display = $tab->appendChild($display);
    
                $displayValue = $doc->createTextNode($t['display']);
                $displayValue = $display->appendChild($displayValue);
                
                $link = $doc->createElement('link');
                $link = $tab->appendChild($link);
    
                $linkValue = $doc->createTextNode($t['link']);
                $linkValue = $link->appendChild($linkValue);

                $submenu = $doc->createElement('submenu');
                $submenu = $tab->appendChild($submenu);
                
                if (isset($t['submenu'])) {
                    foreach ($t['submenu'] as $s) {
    
                        $submenuTab = $doc->createElement('tab');
                        $submenuTab = $submenu->appendChild($submenuTab);
            
                        $module = $doc->createElement('module');
                        $module = $submenuTab->appendChild($module);
            
                        $moduleValue = $doc->createTextNode($s['module']);
                        $moduleValue = $module->appendChild($moduleValue);
            
                        $controller = $doc->createElement('controller');
                        $controller = $submenuTab->appendChild($controller);
            
                        $controllerValue = $doc->createTextNode($s['controller']);
                        $controllerValue = $controller->appendChild($controllerValue);
                        
                        $action = $doc->createElement('action');
                        $action = $submenuTab->appendChild($action);
            
                        $actionValue = $doc->createTextNode($s['action']);
                        $actionValue = $action->appendChild($actionValue);
                        
                        $display = $doc->createElement('display');
                        $display = $submenuTab->appendChild($display);
            
                        $displayValue = $doc->createTextNode($s['display']);
                        $displayValue = $display->appendChild($displayValue);
                        
                        $link = $doc->createElement('link');
                        $link = $submenuTab->appendChild($link);
            
                        $linkValue = $doc->createTextNode($s['link']);
                        $linkValue = $link->appendChild($linkValue);
                    }
                }
            } 
            
            if (!$doc->save($filePath)) {
                $retData = array('rc' => '0', 'msg' => 'Error saving navigation xml file to disk.');
                echo Zend_Json_Encoder::encode($retData);
                return;
            }

            $retData = array('rc' => '1', 'msg' => 'Nav saved successfully');
            echo Zend_Json_Encoder::encode($retData);
            return;
        }
    }
}