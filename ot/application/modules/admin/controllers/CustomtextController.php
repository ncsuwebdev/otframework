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
 * @subpackage Admin_CustomtextController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows the substitution of text strings within the views of the application
 *
 * @package    Admin_CustomtextController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Admin_CustomtextController extends Internal_Controller_Action 
{
	
	/**
	 * The directories in which to look for view templates
	 *
	 * @var array
	 */
    protected $_baseDirs = array(
		                        'application' => './application/views/scripts/',
		                        'ot'          => './ot/application/views/scripts/'
		                    );
	
    /**
     * Shows the tree of templates that have editable text.
     *
     */
    public function indexAction()
    {        
        $this->view->title = 'Custom Text Replacement';

        $files = $this->_makeTree(array('tpl'));
        
        $this->view->files = $files;
        
        $this->view->useTinyMce = true;
        
        $this->view->javascript = "mootree.js";
    }
    
    /**
     * Gets the contents of a file and determines what is available for editing.
     *
     */
    public function getFileAction()
    {
        if ($this->_request->isPost()) {
            
            $this->_helper->getStaticHelper('viewRenderer')->setNoRender();
            $this->_helper->getStaticHelper('layout')->disableLayout();
            
            $filterOptions = array(
                '*' => array(
                    'StringTrim'
                )
            );
            
            $input = new Zend_Filter_Input($filterOptions, array(), $_POST);
            
            $path = $input->path;
            
            $foundFiles = 0;
                        
            foreach ($this->_baseDirs as $directory) {
            	            	
                if (is_file($directory . $path)) {
                    $file = $directory . $path;
                    $foundFiles++;
                }
            }
            
            // we found more than one file with this module/controller/action
            // and this should never happen.
            if ($foundFiles > 1) {
               echo Zend_Json_Encoder::encode(-1);
               return;
            }
            
            
            $configFiles = Zend_Registry::get('configFiles');
            $xml = new Zend_Config_Xml($configFiles['textSubstitution'], 'production');
            $textSub = $xml->toArray();

            $pathParts = explode('/', $path);

            $customData = array();
            
            if (count($pathParts) > 1) {
                $resource = strtolower($pathParts[0] . '_' . $pathParts[1]);
            } else {
                $resource = 'root';   
            }
                
            if (isset($textSub[$resource])) {
                $customData = $textSub[$resource];
            }

            if (isset($pathParts[2])) {
                $actionParts = explode('.', $pathParts[2]);
                $action = $actionParts[0];
            } else {
                $actionParts = explode('.', $pathParts[0]);
                $action = $actionParts[0];    
            }
            
            if (isset($customData[$action]) && is_array($customData[$action])) {
                $customData = $customData[$action];
            } else {
                $customData = array();
            }
            
            $data = file_get_contents($file);
            
            //preg_match_all("/\\{editable id=([^}]*)\\}([^}]*)\\{\/editable\\}/si", $data, $matches); // this regex didn't handle nested smarty tags
            preg_match_all("/\\{editable\b id=([^}]*)\\}(.*?)\\{\/editable\\}/si", $data, $matches);
            
            $originalData = array();
            
            foreach ($matches[0] as $m) {
                //preg_match_all("/\\{editable id=([^}]*)\\}([^}]*)\\{\/editable\\}/si", $m, $vars); // this regex didn't handle nested smarty tags
                preg_match_all("/\\{editable\b id=([^}]*)\\}(.*?)\\{\/editable\\}/si", $m, $vars);
                
                // if the id has single or double quotes around it, remove them
                if (substr($vars[1][0], 0, 1) == "'" || substr($vars[1][0], 0, 1) == '"') {
                    $vars[1][0] = substr($vars[1][0], 1);
                    $vars[1][0] = substr($vars[1][0], 0, strlen($vars[1][0]) -1);
                }
                
                $originalData[] = array($vars[1][0] => trim($vars[2][0]));
            }
                        
            $retData = array();

            foreach ($originalData as $o) {
                
                $key = key($o);
                $value = $o[$key];
                
                $retData[$key] = array('original' => trim($value));
                
                if (isset($customData[$key])) {
                    $retData[$key]['custom'] = html_entity_decode(trim($customData[$key]));
                }
            }
            
            if (count($retData) == 0) {
                echo Zend_Json_Encoder::encode(0);               
            } else {
                echo Zend_Json_Encoder::encode($retData);
            } 
        }
    }
    
    /**
     * Saves the file's variables to the custom text xml file.
     *
     */
    public function saveFileAction()
    {
        $this->_helper->getStaticHelper('viewRenderer')->setNoRender();
        $this->_helper->getStaticHelper('layout')->disableLayout();
        
        if ($this->_request->isPost()) {
            
            $filterOptions = array(
                '*' => array(
                    'StringTrim'
                )
            );
            
            $input = new Zend_Filter_Input($filterOptions, array(), $_POST);

            $path = $input->path;
            
            unset($input->path);
            unset($_POST['path']);
            
            $varsToReset = array();
            if (isset($_POST['varsToReset'])) {
                $varsToReset = $_POST['varsToReset'];
                unset($_POST['varsToReset']);
            }
            
            $pathParts = explode('/', $path);

            if (count($pathParts) > 1) {
                $resource = strtolower($pathParts[0] . '_' . $pathParts[1]);
            } else {
                $resource = 'root';   
            }
                
            if (isset($textSub->$resource)) {
                $customData = $textSub->$resource->toArray();
            }
            
            if (isset($pathParts[2])) {
                $actionParts = explode('.', $pathParts[2]);
                $action = $actionParts[0];
            } else {
                $actionParts = explode('.', $pathParts[0]);
                $action = $actionParts[0];    
            }
            
            $data = array();
            foreach ($_POST as $key => $value) {                
                $data[$key] = $input->getUnescaped($key);
            }
           
            $configFiles = Zend_Registry::get('configFiles');
            $file = $configFiles['textSubstitution'];
            
            if (file_exists($file)) {
                
                $xml = simplexml_load_file($file);
             
            } else {
                $retData = array('rc' => '0', 'msg' => 'Error loading custom text xml file');
                echo Zend_Json_Encoder::encode($retData);
                return;
            }
            
            if (!is_writable($file)) {
                $retData = array('rc' => '0', 'msg' => 'Custom text xml file is not writable');
                echo Zend_Json_Encoder::encode($retData);
                return;
            }
                                    
            foreach ($data as $key => $value) {
                $xml->production->$resource->$action->$key = $value;
            }
            
            foreach ($varsToReset as $v) {
                unset($xml->production->$resource->$action->$v);
            }
            
            $xmlStr = $xml->asXml();
            
            $doc = new DOMDocument("1.0");
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            $doc->loadXml($xmlStr);
            
            if (!$doc->save($file)) {
                $retData = array('rc' => '0', 'msg' => 'Error saving custom text xml file to disk.');
                echo Zend_Json_Encoder::encode($retData);
                return;
            }
        }
        
        $retData = array('rc' => '1', 'msg' => 'Custom values saved successfully');
        echo Zend_Json_Encoder::encode($retData);
        return;
    }
    
    /**
     * Generates the array for the file tree. 
     */
    private function _makeTree($extensions = array()) {
        
        $tree = array();
        foreach ($this->_baseDirs as $directory) {
        	
	        // remove trailing slash
	        if (substr($directory, -1) == "/") {
	            $dir = substr($directory, 0, strlen($directory) - 1);
	        }
        	
            $tree[] = $this->_makeTreeBranch($dir, $extensions);
        }
        return $tree;
    }

    /**
     * Makes the tree for the tree.
     *
     * @param unknown_type $directory
     * @param unknown_type $extensions
     * @return unknown
     */
    private function _makeTreeBranch($directory, $extensions = array()) {
        
        if (preg_match('/\.svn/', $directory)) {
            return;
        }
        
        // get and sort directories/files
        $file = scandir($directory);
        natcasesort($file);
        
        // Make directories first
        $files = $dirs = array();
        
        foreach ($file as $f) {
            if (is_dir("$directory/$f") && !preg_match('/\.svn/', "$directory/$f")) {
                $dirs[] = $f;
            } else {
                $files[] = $f;
            }
        }
        
        $tree = array();
        
        $file = array_merge($dirs, $files);

        // filter unwanted extensions
        if (!empty($extensions)) {
            foreach (array_keys($file) as $key => $value) {
                if (!is_dir("$directory/$file[$key]")) {
                    $ext = substr($file[$key], strrpos($file[$key], ".") + 1); 
                    if(!in_array($ext, $extensions)) {
                        unset($file[$key]);
                    }
                }
            }
        }
        
        foreach ($file as $f) {
            
            if ($f != "." && $f != ".." && !preg_match('/\.svn/', $f)) {
                
                if (is_dir("$directory/$f") && !preg_match('/\.svn/', "$directory/$f")) {
                    // directory
                    $tree[$f] = $this->_makeTreeBranch("$directory/$f", $extensions);
                } else {
                    // file
                    $tree[$f] = $f;
                }
            }
        }
       
        return $tree;
    }
}