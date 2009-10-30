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
        foreach ($this as $item) {
            if ($item->type == 'text/css' && $item->conditionalStylesheet === false) {
                $stylesheets[$item->media][] = $item->href;
            } else {
                $items[] = $this->itemToString($item);
            }
        }
        foreach ($stylesheets as $media=>$styles) {
            $item = new stdClass();
            $item->rel = 'stylesheet';
            $item->type = 'text/css';
            $item->href = $this->getMinUrl() . '?f=' . implode(',', $styles);
            $item->media = $media;
            $item->conditionalStylesheet = false;
            $items[] = $this->itemToString($item);
        }
        return $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
    }
    public function getMinUrl() {
        return $this->getBaseUrl() . '/min/';
    }
    
    public function getBaseUrl(){
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}