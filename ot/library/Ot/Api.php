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
 * @package    Ot_Api
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Provides an interface for remote procedure calls
 *
 * @package   Ot_Api
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Api
{   
	/**
	 * Gets the versions of the available frameworks
	 *
	 * @return Array key => value array of frameworkName => Version
	 */
	public static function getVersions()
	{
		return array(
			'OTFramework' => Ot_Version::VERSION,
			'ZendFramework' => Zend_Version::VERSION,
		);
	}
	
	/**
	 * Gets the value of a user config variable
	 *
	 * @param string $option The name of the variable to get
	 * @return string
	 */
	public static function getConfigOption($option)
	{
		$config = Zend_Registry::get('config');
		
		if (isset($config->user->{$option})) {
			return $config->user->{$option}->val;
		}
		
		throw new Ot_Exception_Data('msg-error-optionNotFound');
	}
	
	/**
	 * Returns the account information for the currently authenticated user.
	 * 
	 * @return array The account info
	 */
	public static function getMyAccount()
	{
	   $otAccount = new Ot_Account();
	   
	   if (!Zend_Auth::getInstance()->hasIdentity()) {
	       throw new Ot_Exception_Access('msg-error-apiAccessDenied');
	   }
	   
	   $accountId = Zend_Auth::getInstance()->getIdentity()->accountId;
	   
       $accountInfo = $otAccount->find($accountId);
       
       if (is_null($accountInfo)) {
          throw new Ot_Exception_Data('msg-error-noAccount');
       }
       
       $accountInfo = $accountInfo->toArray();
       unset($accountInfo['password']);
       
       return $accountInfo;
	}
	
	/**
	 * Update the account of the currently logged in user.  This is the user
	 * that the API is being used as, not the consumer itself.
     * 
     * @param string $firstName The new first name
     * @param string $lastName The new last name
     * @param string $emailAddress The new email address
     * @param string $timezone A valid timezone string
	 * 
	 * @return boolean
	 */
	public static function updateMyAccount($firstName, $lastName, $emailAddress, $timezone)
	{
	    if (!Zend_Auth::getInstance()->hasIdentity()) {
	        throw new Ot_Exception_Access('msg-error-apiAccessDenied');
        }
	    
	    if (!in_array($timezone, Ot_Timezone::getTimezoneList())) {
            throw new Ot_Exception_Data('msg-error-invalidTimezone');
        }
        
        $otAccount = new Ot_Account();
        
        $accountId = Zend_Auth::getInstance()->getIdentity()->accountId;
        
        $data = array(
                       'accountId'    => $accountId,
                       'firstName'    => $firstName,
                       'lastName'     => $lastName,
                       'emailAddress' => $emailAddress,
                       'timezone'     => $timezone
                     );
                     
        $otAccount->update($data, null);
        
        return true;
	}
	
	/**
	 * Gets an account from the system
	 * 
	 * @param int $accountId The account to fetch
	 * 
	 * @return array
	 */
	public static function getAccount($accountId)
	{
	    $otAccount = new Ot_Account();
	    $accountInfo = $otAccount->find($accountId)->toArray();
	    
	    if (is_null($accountInfo)) {
	       throw new Ot_Exception_Data('msg-error-noAccount');
	    }
	    
        $accountInfo = $accountInfo->toArray();
        unset($accountInfo['password']);
        
        return $accountInfo;
	}
	
	/**
	 * Update an account's info
	 * 
	 * @param int $accountId The id of the account to update
	 * @param string $firstName The new first name
	 * @param string $lastName The new last name
	 * @param string $emailAddress The new email address
	 * @param string $timezone A valid timezone string
	 * 
	 * @return boolean
	 */
	public static function updateAccount($accountId, $firstName, $lastName, $emailAddress, $timezone)
	{	    
	    if (!in_array($timezone, Ot_Timezone::getTimezoneList())) {
	        throw new Ot_Exception_Data('msg-error-invalidTimezone');
	    }
	    
	    $otAccount = new Ot_Account();
	    
	    $data = array(
	                   'accountId'    => $accountId,
	                   'firstName'    => $firstName,
	                   'lastName'     => $lastName,
	                   'emailAddress' => $emailAddress,
	                   'timezone'     => $timezone
	                 );
	                 
        $otAccount->update($data, null);
        
        return true;
	}
	
	/**
	 * Returns all the cron jobs in the system, their last run date, and
	 * their status
	 * 
	 * @return array of cron jobs
	 */
	public static function getCronJobs()
	{
	    $cron = new Ot_Cron_Status();
	    return $cron->getAvailableCronJobs();
	}
	
	/**
	 * Sets the status for a cron job
	 * 
	 * @param string $name The name of the cron job
	 * @param string $status The status to set the cron job to (enabled or disabled)
	 * @return boolean
	 */
	public static function setCronJobStatus($name, $status)
	{
	    $status = strtolower($status);
	    if (!in_array($status, array('enabled', 'disabled'))) {
	        throw new Ot_Exception_Data('msg-error-invalidStatus');
	    }
	    
	    $cron = new Ot_Cron_Status();
	    return $cron->setCronStatus($name, $status);
	}
	
	/**
	 * Returns the bug reports in the system.  You can optionally specify the
	 * types of bugs to return
	 * 
	 * @param array $status The bug type to return.  Can be (new, ignore, escalated, fixed).
	 *                      null will return all bugs. 
	 * 
	 * @return array
	 */
	public static function getBugReports($status = null)
	{
        $otBug = new Ot_Bug();
        
        if (!is_null($status) && !in_array(strtolower($status), array('new', 'ignore', 'escalated', 'fixed'))) {
            throw new Ot_Exception_Data('msg-error-invalidStatus');
        }
        
        if (!is_null($status)) {
            $where = $otBug->getAdapter()->quoteInto('status = ?', strtolower($status));
        } else {
            $where = null;
        }
        
        $bugs = $otBug->fetchAll($where, 'submitDt DESC')->toArray();

        $bugText   = new Ot_Bug_Text();
        $otAccount = new Ot_Account();
        
        foreach ($bugs as &$b) {
            
            $text = $bugText->getBugText($b['bugId'])->toArray();

            foreach ($text as &$t) {
                $accountInfo = $otAccount->find($t['accountId']);
                
                $t['userInfo'] = array(
                                    'accountId'    => $accountInfo->accountId,
                                    'username'     => $accountInfo->username,
                                    'realm'        => $accountInfo->realm,
                                    'firstName'    => $accountInfo->firstName,
                                    'lastName'     => $accountInfo->lastName,
                                    'emailAddress' => $accountInfo->emailAddress
                                 );
                $b['text'] = $t;
            }
            
        }       
        
        return $bugs;  
	}
	
	/**
	 * Describes all available methods for the API
	 *
	 * @return array of methods, descriptions, and arguments
	 */
	public static function describe()
	{
		$rc = new ReflectionClass('Internal_Api');

		$ret = array();
		foreach ($rc->getMethods() as $ref) {
			
			$desc = $ref->getDocComment();
			
			$returnDoc = '';
			preg_match('/@return[^\n]*/', $desc, $returnDoc);
			
			if (count($returnDoc) != 0) {
				$returnDoc = preg_replace('/@return/', '', $returnDoc[0]);
			} else {
				$returnDoc = '';
			}
			
			$desc = preg_replace('/@[^\n]*/', '', $desc);
	        $desc = preg_replace('/\s*\*\s/', ' ', $desc);
	        $desc = trim(preg_replace('/(\/\*|\*\/)*/', '', $desc));
        			
			$temp = array(
			 'method'      => $ref->getName(),
			 'description' => $desc,
			 'return'      => $returnDoc,
			 'args'        => array(),
			);
			
			$params = $ref->getParameters();
			foreach ($params as $p) {
				$temp['args'][] = $p->name;
			}
			
			$ret[] = $temp;
		}
		
		usort($ret, create_function('$a, $b', "return strnatcasecmp(\$a['method'], \$b['method']);"));
		
		return $ret;		
	}
}