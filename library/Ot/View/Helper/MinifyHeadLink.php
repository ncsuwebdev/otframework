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
 * @package    Ot_View_Helper_MinifyHeadLink
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Minifies the stylesheets added via the minifyHeadLink helper using 
 * minify (http://code.google.com/p/minify/)
 *
 * @package    Ot_View_Helper_MinifyHeadLink
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */

class Ot_View_Helper_MinifyHeadLink extends Zend_View_Helper_HeadLink
{
    
    protected $_regKey = 'Ot_View_Helper_MinifyHeadLink';
    
    public function minifyHeadLink(array $attributes = null,
        $placement = Zend_View_Helper_Placeholder_Container_Abstract::APPEND)
    {
        return parent::headlink($attributes, $placement);
    }
    
    public function toString($indent = null)
    {
        $items = array();
        $stylesheets = array();
        $baseUrl = $this->getBaseUrl();
                 
        foreach ($this as $item) {
            if ($item->type == 'text/css' && $item->conditionalStylesheet === false) {
                $stylesheets[$item->media][] = preg_replace('/^' . preg_quote($baseUrl, '/') . '/i', '', $item->href);
            } else {
                $items[] = $this->itemToString($item);
            }
        }
        
        // Remove the slash at the beginning if there is one
        if (substr($baseUrl, 0, 1) == '/') {
            $baseUrl = substr($baseUrl, 1);
        }
        foreach ($stylesheets as $media=>$styles) {
            $item = new stdClass();
            $item->rel = 'stylesheet';
            $item->type = 'text/css';
            
            if ($baseUrl == '') {            
                $item->href = $this->getMinUrl() . '?f=' . implode(',', $styles);
            } else {
                $item->href = $this->getMinUrl() . '?b=' . $baseUrl . '&f=' . implode(',', $styles);
            }
            $item->media = $media;
            $item->conditionalStylesheet = false;
            $items[] = $this->itemToString($item);
        }
        
        // if there's nothing to minify, don't echo the link
        if(count($items) == 0) {
            return '';
        }
        
        $link = implode($this->_escape($this->getSeparator()), $items);
        
        return $link;
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