<?php

/**
 * Minifies the javascript files added via the HeadScript helper using 
 * minify (http://code.google.com/p/minify/)
 *
 */
class Zend_View_Helper_MinifyHeadScript extends Zend_View_Helper_HeadScript
{
    public function minifyHeadScript()
    {
        $indent = "    ";
        $items = array();
        $scripts = array();
        
        foreach ($scripts as $script) {
            $item = new stdClass();
            $item->type = 'text/javascript';
            $item->src = $this->getMinUrl() . '?f=' . implode(',', $script);
            $items[] = $item->toString($item);
        }
        return $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
    }
    public function getMinUrl() {
        return $this->getBaseUrl() . '/min/';
    }
    
    public function getBaseUrl(){
        return Zend_View_Helper_BaseUrl::baseUrl();
    }
}