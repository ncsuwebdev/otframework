<?php
/**
 * Website
 *
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
 * @package    Website
 * @subpackage Image
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: Semester.php 188 2007-07-31 17:59:10Z jfaustin@EOS.NCSU.EDU $
 */

/**
 *
 * @package    Website
 * @subpackage PortalLink
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 *
 */
class Ot_Api_Code extends Ot_Db_Table 
{
    /**
     * Database table name
     *
     * @var string
     */
    public $_name = 'tbl_ot_api_code';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'userId';

    protected $_apiCodeUser = '';

    public function generateCodeForUser($userId)
    {
        $data = array(
            'userId' => $userId,
            'code'   => md5(microtime())
        );
        
        $thisCode = $this->find($userId);
        
        if (is_null($thisCode)) {
            parent::insert($data);
        } else {
        	parent::update($data, null);
        }
        
        return $data['code'];
    }

    public function verify($accessCode)
    {
    	$where = $this->getAdapter()->quoteInto('code = ?', $accessCode);
    	$this->_messages[] = $where;
    	$result = $this->fetchAll($where, null, 1);
    	
    	if ($result->count() != 1) {
    		throw new Exception('Code not found');
    	}
    	
    	$this->_apiCodeUser = $result->current()->userId;
    	
        $config = Zend_Registry::get('config');
        Zend_Loader::loadClass($config->authorization);

        $authz = new $config->authorization;   
        
        $result = $authz->getUser($this->_apiCodeUser);
    }
    
    public function getApiUserId()
    {
    	return $this->_apiCodeUser;
    }
}
