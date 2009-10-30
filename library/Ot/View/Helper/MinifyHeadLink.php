<?php

/**
 * Minifies the stylesheets added via the Headlink helper using 
 * minify (http://code.google.com/p/minify/)
 *
 */
class Ot_View_Helper_MinifyHeadLink extends Zend_View_Helper_HeadLink
{
    public function minifyHeadLink()
    {
        $indent = "    ";
        $items = array();
        $stylesheets = array();
        $baseUrl = $this->getBaseUrl();
         
        foreach ($this as $item) {
            if ($item->type == 'text/css' && $item->conditionalStylesheet === false) {
                $stylesheets[$item->media][] = str_replace($baseUrl, '', $item->href);
            } else {
                $items[] = $this->itemToString($item);
            }
        }
        
        //remove the slash at the beginning if there is one
        if (substr($baseUrl, 0, 1) == '/') {
            $baseUrl = substr($baseUrl, 1);
        }
        
        foreach ($stylesheets as $media=>$styles) {
            $item = new stdClass();
            $item->rel = 'stylesheet';
            $item->type = 'text/css';
            $item->href = $this->getMinUrl() . '?b=' . $baseUrl . '&f=' . implode(',', $styles);
            $item->media = $media;
            $item->conditionalStylesheet = false;
            $items[] = $this->itemToString($item);
        }
        
        
        $link = $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
        
        return $link;
    }
    public function getMinUrl() {
        return $this->getBaseUrl() . '/min/';
    }
    
    public function getBaseUrl(){
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}