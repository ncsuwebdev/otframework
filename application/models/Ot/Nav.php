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
 * @package    Ot_Nav
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with the navigation.
 *
 * @package    Ot_Nav
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Nav extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_nav';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Cache key to store nav results
     *
     */
    const CACHE_KEY = 'Ot_Nav';
    
    /**
     * Gets the nav from the cache if it exists.  If not, it gets 
     * it from the database.
     */
    public function getNav()
    {
        $cache = Zend_Registry::get('cache');
        
        if (!$navData = $cache->load('Ot_Nav')) {
            
            $navData = $this->fetchAll();
            
            $cache->save($navData, 'Ot_Nav');
        }
        
        return $navData;
    }
    
    /**
     * Returns the nav html string
     * 
     * @var $navData  The array of the nav data
     * @var addTitles Whether or not to add the module:controller:action string
     *                in the title attribute
     */
    public function generateHtml($navData, $addTitles = false)
    {
        $str = '';        
        foreach ($navData['children'] as $c) {
            if ($c['show']) {
                $str .= '<li name="' . $c['display'] . '" id="navItem_' . $c['parent'] . '_' . $c['id'] . '">';
                
                $title = '';
                if ($addTitles) {
                    $title = 'title="' . $c['module'] . ':' . $c['controller'] . ':' . $c['action'] . '"';
                }
                
                if ($c['allowed']) {
                    $str .= '<a' . ($title ? ' ' . $title : '') . ' href="' . $c['link'] . '" target="'
                         . $c['target'] . '"' . (($c['link'] == '') ? ' class="no-link"' : '') . '>' . $c['display'] . '</a>' . "\n";
                }
                
                if (count($c['children']) != 0) {
        
                    $str .= '<ul>' . "\n";
                    $str .= $this->generateHtml($c, $addTitles);
                    $str .= '</ul>' . "\n";
                }
        
                $str .= "</li>";
            }
        }

        return $str;
    }
    
    /**
     * Overrides the insert method to clear the cache after the insert takes place
     */
    public function insert(array $data)
    {
        parent::insert($data);
        $this->_clearCache();
    }
    
    /**
     * Overrides the update method to clear the cache after the delete takes place
     */
    public function update(array $data, $where)
    {
        parent::update($data, $where);
        $this->_clearCache();
    }
    
    /**
     * Overrides the delete method to clear the cache after the delete takes place
     */
    public function delete($where)
    {
        parent::delete($where);
        $this->_clearCache();
    }
    
    /**
     * Clears the cached nav file
     *
     */
    protected function _clearCache()
    {
        $cache = Zend_Registry::get('cache');
        $cache->remove(self::CACHE_KEY);
    }
}