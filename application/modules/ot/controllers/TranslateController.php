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
 * @package    Ot_TranslateController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Allows an admin to edit the translation files
 *
 * @package    Ot_TranslateController
 * @category   Controller
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_TranslateController extends Zend_Controller_Action
{
    /**
     * Shows the backup index page
     */
    public function indexAction()
    {                               
        $m = $this->_getParam('m');
        $c = $this->_getParam('c');
        $a = $this->_getParam('a');
        
        $req = $m . '-' . $c . '-' . $a;
        
        $translate = Zend_Registry::get('Zend_Translate');
        
        $messages = $translate->getMessages($translate->getLocale());
        
        $actionMessages = array();
        foreach ($messages as $key => $value) {
            if (preg_match('/^' . $req . '/i', $key)) {
                $actionMessages[$key] = array(
                    'title' => preg_replace('/^' . $req . ':/i', '', $key),
                    'value' => $value,
                );
            }
        }                        
        
        $this->_helper->layout()->disableLayout();
        
        $this->view->assign(array(
            'translationTable' => $actionMessages,
        ));
    }
    
    public function saveAction()
    {        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();
        
        if (!$this->_request->isPost() || !$this->getRequest()->isXmlHttpRequest()) {
            throw new Ot_Exception_Access('You can not access this method directly');
        }
            
        $path = realpath(APPLICATION_PATH . '/../overrides/languages');

        if (!is_writable($path)) {                       
            $retData = array('rc' => '0', 'msg' => $this->view->translate('msg-error-langDirNotWritable'));
            echo Zend_Json::encode($retData);
            return;
        }
        
        $postFilter = Zend_Registry::get('postFilter');

        $overrides = array();
        $removals  = array();

        foreach ($postFilter->getEscaped() as $key => $value) {
            if (preg_match('/^[^#]*#[a-z]*$/i', $key)) {
                $removals[] = preg_replace('/#.*$/i', '', $key);
            } else {
                if ($value != '') {
                    $overrides[$key] = $value;
                }                                    
            }
        }

        ini_set('auto_detect_line_endings', TRUE);

        $newData = array();
                
        $translate = Zend_Registry::get('Zend_Translate');
        $filename = $path . '/' . $translate->getLocale() . '.csv';

        if (is_file($filename)) {

            $handle = fopen($filename, 'r+');

            while (($data = fgetcsv($handle, 0, ";")) !== FALSE ) {
                if (isset($overrides[$data[0]])) {
                    $data[1]   = $overrides[$data[0]];
                    $newData[] = $data;
                    unset($overrides[$data[0]]);
                } elseif (!in_array($data[0], $removals)) {
                    $newData[] = $data;
                }
            }

            rewind($handle);
            ftruncate($handle, 0);
        } else {
            $handle = fopen($filename, 'w+');
        }

        foreach ($overrides as $key => $value) {
            $newData[] = array($key, $value);
        }

        foreach ($newData as $d) {

            $ret = fputcsv($handle, $d, ";");

            if ($ret === false) {                        
                $retData = array('rc' => '0', 'msg' => $this->view->translate('msg-error-writingLangFile'));
                echo Zend_Json::encode($retData);
                return;
            }
        }                        

        ini_set('auto_detect_line_endings', FALSE);

        Zend_Translate::clearCache();

        $logOptions = array('attributeName' => 'translation', 'attributeId' => $translate->getLocale());

        $this->_helper->log(Zend_Log::INFO, 'Language override file modified', $logOptions);

        $retData = array('rc' => '1', 'msg' => $this->view->translate('msg-info-savedLang'));
        echo Zend_Json::encode($retData);
        return; 
        
    }
}