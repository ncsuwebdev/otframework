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
class Ot_Model_DbTable_Nav extends Ot_Db_Table
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
        
        if (!$navigation = $cache->load('Ot_Navigation_Container')) {
            $navData = $this->fetchAll();
        
            $pages = array();

            foreach ($navData as $tab) {

                $page = array(
                    'id'         => $tab->id,
                    'parent'     => $tab->parent,
                    'label'      => $tab->display,     
                    'module'     => $tab->module,
                    'controller' => $tab->controller,
                    'action'     => $tab->action,
                    'route'      => 'default',
                    'resource'   => $tab->module . '_' . (($tab->controller == '') ? 'index' : $tab->controller),
                    'privilege'  => ($tab->action == '') ? 'index' : $tab->action
                );                                      
                
                if (preg_match('/^http/i', $tab->link)) {
                    unset($page['module']);
                    unset($page['controller']);
                    unset($page['action']);
                    unset($page['route']);
                    
                    $page['uri'] = $tab->link;
                    $page['target'] = '_blank';
                }
                
                $pages[] = $page;
            }                        
            
            $tree = $this->_buildTree($pages);
            
            $navigation = new Zend_Navigation($tree); 
            
            $cache->save($navigation, 'Ot_Navigation_Container');
        }

        return $navigation;
        
    }
        
       
    protected function _buildTree($allNodes, $node = null)
    {
        $children = array();
        
        $parentId = (is_null($node)) ? 0 : $node['id'];
        
        foreach ($allNodes as $key => $n) {            
            if ($n['parent'] == $parentId) {
                unset($n['parent']);
                
                $children[] = $n;
            }
        }

        foreach ($children as $key => $child) {
            
            $kids = $this->_buildTree($allNodes, $child);
            
            $keepers = array();
            foreach ($kids as $k) {
                $keepers[] = $k;                
            }
            
            if (count($keepers) != 0) {
                $children[$key]['pages'] = $keepers;
            }
        }
                    
        return $children;        
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