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
 * @package    Ot_View_Helper_MinifyHeadScript
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Minifies the javascript files added via the minifyHeadScript helper using 
 * minify (http://code.google.com/p/minify/)
 *
 * @package    Ot_View_Helper_MinifyHeadScript
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_MinifyHeadScript extends Zend_View_Helper_HeadScript
{
    
    protected $_regKey = 'Ot_View_Helper_MinifyHeadScript';
    
    public function minifyHeadScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null,
        $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        return parent::headScript($mode, $spec, $placement, $attrs, $type);
    }
    
    public function toString($indent = null)
    {
        $items = array();
        $scripts = array();
        $baseUrl = $this->getBaseUrl();
        
        // We can only support files
        foreach ($this as $item) {
            if (isset($item->attributes['src']) && !empty($item->attributes['src'])) {
                $scripts[] = preg_replace('/^' . preg_quote($baseUrl, '/') . '/i', '', $item->attributes['src']);
            }
        }
        
        // if there's nothing to minify, don't dump the link
        if(count($scripts) == 0) {
            return '';
        }
        
        // Remove the slash at the beginning if there is one
        if (substr($baseUrl, 0, 1) == '/') {
            $baseUrl = substr($baseUrl, 1);
        }
        
        $item = new stdClass();
        $item->type = 'text/javascript';
        
        if ($baseUrl == '') {
            $item->attributes['src'] = $this->getMinUrl() . '?f=' . implode(',', $scripts);
        } else {
            $item->attributes['src'] = $this->getMinUrl() . '?b=' . $baseUrl . '&f=' . implode(',', $scripts);
        }
        
        $scriptTag = $this->itemToString($item, '', '', '');
        return $scriptTag;
    }
    
    public function getMinUrl()
    {
        return $this->getBaseUrl() . '/min/';
    }
    
    public function getBaseUrl()
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}