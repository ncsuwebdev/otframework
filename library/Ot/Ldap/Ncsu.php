<?php

/**
 * PHP 5 only
 *
 * This LDAP module allows easy interaction between PHP and NCSU's LDAP server.  The
 * module extends the main LDAP driver which allows for persistant connections.
 *
 * @category  Ot
 * @package   Ldap
 * @author    Jason Austin
 * @copyright 2007 NC State University Information Technology Division - IT App Dev
 *
 */

class Ot_Ldap_Ncsu extends Ot_Ldap_Driver
{

    /**
     * Default context for most searches
     *
     * @var string
     */
    protected $_defaultContext = "ou=people,dc=ncsu,dc=edu";


    /**
     * Constructor for LDAP NCSU
     *
     */
    public function __construct()
    {}


	/**
	 * Convenience function to search on first and last name
	 *
	 * @param string $firstName - The first name (or part of the first name)
	 * @param string $lastName - The last name (or part of the last name)
	 * @param bool $allFields - Boolean to return all or just selected fields
	 * @return LDAP result set
	 * @throws Ot_Resource_Exception on LDAP Error from Ot_Ldap_Driver::search()
	 */
	public function searchOnFirstAndLastName($firstName, $lastName, $allFields = false)
	{

	    // Setup return fields
		if ($allFields) {
			$this->setReturnFieldsToDefault();
		} else {
			$this->addReturnField("uid");
			$this->addReturnField("cn");
			$this->addReturnField("givenName");
			$this->addReturnField("ncsuMiddleName");
			$this->addReturnField("sn");
			$this->addReturnField("telephoneNumber");
			$this->addReturnField("ncsuCampusID");
		}

		// Do search based on data passed to the function
		if ($firstName == "" || $lastName == "") {
			if ($firstName == "") {
				return $this->search("(sn=" . $lastName . "*)", $this->_defaultContext);
			}

			return $this->search("(givenName=" . $firstName . "*)", $this->_defaultContext);
		}

		return $this->search("(&(givenName=" . $firstName . "*)(sn=" . $lastName . "*))", $this->_defaultContext);

	}


	/**
	 * Convenience function to search on phone numbers
	 *
	 * @param string $phone - The phone number to search for
	 * @param bool $allFields - Boolean to return all or just selected fields
	 * @throws Ot_Resource_Exception on LDAP Error from Ot_Ldap_Driver::search()
	 * @return LDAP Result Set
	 */
	public function searchOnPhone($phone, $allFields = false)
	{

	    // Setup return fields
		if ($allFields) {
			$this->setReturnFieldsToDefault();
		} else {
			$this->addReturnField("uid");
			$this->addReturnField("cn");
			$this->addReturnField("givenName");
			$this->addReturnField("ncsuMiddleName");
			$this->addReturnField("sn");
			$this->addReturnField("telephoneNumber");
			$this->addReturnField("ncsuCampusID");
		}

    	return $this->search("telephoneNumber=" . $phone, $this->_defaultContext);
	}


	/**
	 * Convenience function to return all users in a group
	 *
	 * @param string $group - Name of the group to search on
	 * @param bool $allFields - Boolean to return all or just selected fields
	 * @return LDAP Result set
	 * @throws Ot_Resource_Exception on LDAP Error from Ot_Ldap_Driver::search()
	 */
	public function getUsersInGroup($group, $allFields = false)
	{

	    // Setup return fields
		if ($allFields) {
			$this->setReturnFieldsToDefault();
		} else {
			$this->addReturnField("memberUid");
		}

		// Do the search
		$result = $this->search("cn=" . $group, "ou=keyserver,ou=groups,dc=ncsu,dc=edu");

		// Strip out only the important stuff
		$result = $result[0]["memberuid"];
		unset($result["count"]);

		return $result;


	}

	/**
	 * Convenience funciton to return all groups a user is in
	 *
	 * @param string $userId - Unity ID of the user in question
	 * @return LDAP Result set
	 * @throws Ot_Resource_Exception on LDAP Error from Ot_Ldap_Driver::search()
	 */
	public function getGroupsForUser($userId)
	{

	    // Setup return fields
	    $this->addReturnField("memberNisNetgroup");

	    $result = $this->search("uid=" . $userId, "ou=accounts,dc=ncsu,dc=edu");

	    if (count($result) == 0) {
	        return array();
	    }

	    $result = $result[0]["membernisnetgroup"];

	    unset($result["count"]);

	    return $result;

	}


	/**
	 * Convenience function to return data based on a user id
	 *
	 * @param string $userId - Users unity id
	 * @param bool $allFields - Boolean to return all or just selected fields
	 * @return LDAP Result Set
	 * @throws Ot_Resource_Exception on LDAP Error from Ot_Ldap_Driver::search()
	 */
	public function lookupByUserId($userId, $allFields = false)
	{

		if ($allFields) {
			$this->setReturnFieldsToDefault();
		} else {
			$this->addReturnField("uid");
			$this->addReturnField("cn");
			$this->addReturnField("givenName");
			$this->addReturnField("ncsuMiddleName");
			$this->addReturnField("sn");
			$this->addReturnField("telephoneNumber");
			$this->addReturnField("ncsuPrimaryEmail");
			$this->addReturnField("ncsuPrimaryRole");
			$this->addReturnField("gidNumber");
			$this->addReturnField("ncsuCampusID");
		}

		return $this->search("uid=" . $userId, $this->_defaultContext);
	}


	/**
	 * Convenience function to return data based on a campus ID number
	 *
	 * @param string $cid - Campus ID number
	 * @param string $allFields - Boolean to return all or just selected fields
	 * @return LDAP Result Set
	 * @throws Ot_Resource_Exception on LDAP Error from Ot_Ldap_Driver::search()
	 */
	public function lookupByCampusId($cid, $allFields = false)
	{

		if($allFields){
			$this->setReturnFieldsToDefault();
		} else {
			$this->addReturnField("uid");
			$this->addReturnField("cn");
			$this->addReturnField("givenName");
			$this->addReturnField("ncsuMiddleName");
			$this->addReturnField("sn");
			$this->addReturnField("telephonenumber");
			$this->addReturnField("ncsuCampusID");
		}

		return $this->search("ncsuCampusID=" . $cid, $this->_defaultContext);
	}


	/**
	 * Clean's the LDAP Result Set into an associative array, stripped on any
	 * unnecessary stuff.
	 *
	 * @param array $result - LDAP Result Set
	 * @return array
	 */
	public function cleanLDAPResult($result)
	{
		unset($result["count"]);

		$ret = array();
		foreach ($result as $r) {
			$temp = array();

			$keys = array_keys($r);

			foreach ($keys as $k) {

				if (!is_int($k) && $k != "dn" && $k != "count") {
					if (is_array($r[$k])) {
						$temp[$k] = $r[$k][0];
					} else {
						$temp[$k] = $r[$k];
					}
				}
			}

			$ret[] = $temp;
		}

		return $ret;

	}


	/**
	 * Sorts the LDAP Result
	 *
	 * @param array $result - Cleaned LDAP Result
	 * @param string $key - Key to sort on
	 * @param string $order - Order to sort ("asc" or "desc")
	 * @return sorted array
	 */
	public function sortLDAPResult($result, $key, $order = "asc")
	{
	   usort($result, create_function('$a, $b', "return strnatcasecmp(\$a['$key'], \$b['$key']);"));

	   if ($order == "desc") {
		   $result = array_reverse($result);
	   }

	   return($result);
	}


	/**
	 * Builds an associative array based on attribute and value returned
	 * in the LDAP result set.
	 *
	 * @param ldapResult - LDAP result set
	 * @return formatted associative array
	 * @deprecated - Deprecated as of 1/25/2007 - JFA
	 */
	protected function _buildLDAPResultArray($ldapResult)
	{

		$all_info = array();

		$ct = @ldap_count_entries($this->_link, $ldapResult);

		for ($i = 0; $i < $ct; $i++) {

			$user_info = array();

			// basic information
			$user_info['uid'] = $uid;

			$user_info['info']['has_account'] = FALSE;
			$account_info = array();

			$user_info['info']['is_employee'] = FALSE;
			$employee_info = array();

			$user_info['info']['is_student'] = FALSE;
			$student_info = array();

			for ($entry_id = @ldap_first_entry($this->_link, $ldapResult); $entry_id != FALSE; $entry_id = @ldap_next_entry($this->_link, $entry_id)) {


			        $this_dn = @ldap_get_dn($this->_link, $entry_id);
			        $this_entry = @ldap_get_attributes($this->_link, $entry_id);

			        // parse dn
			        $temp = explode(",", $this_dn);
			        $checkou = $temp[1];

			        switch ($checkou) {

					case "ou=accounts":
						$user_info['info']['has_account'] = TRUE;
						$data_info = &$account_info;
						break;

					case "ou=employees":
						$user_info['info']['is_employee'] = TRUE;
						$data_info = &$employee_info;
						break;

					case "ou=students":
						$user_info['info']['is_student'] = TRUE;
						$data_info = &$student_info;
						break;

					default:
						continue 2;
			        }

			        foreach ($this_entry as $attribute => $value) {
					   if (!(is_array($value))) {
					       continue;
					   }

					   if ($attribute == "uid") {
					       continue;
					   }

					   if ($attribute == "count") {
					       continue;
					   }

					   if ($value['count'] > 1) {
						  $data_info[$attribute] = $value;
						  unset($data_info[$attribute]['count']);
					   } else {
						  $data_info[$attribute] = $value[0];
					   }
			        }
			}


			// merge information student, then employee, then account

			if ($user_info['info']['is_student']) {
				$user_info = array_merge($user_info, $student_info);
				$user_info['info']['student'] = $student_info;
			}

			if ($user_info['info']['is_employee']) {
				$user_info = array_merge($user_info, $employee_info);
				$user_info['info']['employee'] = $employee_info;
			}

			if ($user_info['info']['has_account']) {
				$user_info = array_merge($user_info, $account_info);
				$user_info['info']['account'] = $account_info;
			}

			$all_info = $user_info;
		}

		return $all_info;

	}

}

?>
