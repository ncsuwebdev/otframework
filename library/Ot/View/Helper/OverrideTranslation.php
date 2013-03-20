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
    public function overrideTranslation($text = 'Edit Text')
    {
        $zcf = Zend_Controller_Front::getInstance();
        
        $request = $zcf->getRequest();
        
        $registry = new Ot_Config_Register();

        $acl    = Zend_Registry::get('acl');
        $auth   = Zend_Auth::getInstance();
        
        $role = (!$auth->hasIdentity()) ? $registry->defaultRole->getValue() : $auth->getIdentity()->role;
        
        $html = array();
        
        if ($acl->isAllowed($role, 'ot_translate', 'index')) {
            
            $html[] = '<a href="#overrideTranslationModal" data-toggle="modal">' . $text . '</a>';
            
            // modal
            $html[] = '<div id="overrideTranslationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
            $html[] = '  <div class="modal-header">';
            $html[] = '    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
            $html[] = '    <h3 id="myModalLabel">Edit Text On This Page</h3>';
            $html[] = '  </div>';
            $html[] = '  <div class="modal-body">';
            $html[] = '    <p>' . $this->view->translate("ot-translate-index:header") . '</p>';
            $html[] = '    <input type="hidden" id="overrideTranslation_m" value="' . $request->getModuleName() . '"/>';
            $html[] = '    <input type="hidden" id="overrideTranslation_c" value="' . $request->getControllerName() . '"/>';
            $html[] = '    <input type="hidden" id="overrideTranslation_a" value="' . $request->getActionName() . '"/>';
            $html[] = '    <div id="overrideTranslationContent"></div>';
            $html[] = '  </div>';
            $html[] = '  <div class="modal-footer">';
            $html[] = '    <button class="btn btn-primary" id="overriteTranslationSave">Save changes</button>';
            $html[] = '    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>';
            $html[] = '  </div>';
            $html[] = '</div>';
            
            //$html[] = $this->view->url(array('controller' => 'translate', 'm' => $request->getModuleName(), 'c' => $request->getControllerName(), 'a' => $request->getActionName()), 'ot',true);
            
            
        }
        
        return join(PHP_EOL, $html);
    }
}