<?php

/**
 * PHP 5 only
 *
 * This LDAP module allows easy interaction between PHP and NCSU's LDAP server.  The
 * modules allows for a persistant connection during the execution of the entire
 * script so that more than one stream is not opened to the LDAP server.
 *
 * @category  Ot
 * @package   Ldap
 * @author    Jason Austin
 * @copyright 2007 NC State University Information Technology Division - IT App Dev
 *
 */
class Ot_Ldap_Driver
{

    /**
     * The LDAP link object
     *
     * @var resource
     */
	protected $_link = NULL;

	/**
	 * Resources that are active
	 *
	 * @var array
	 */
	protected $_resources = array();

	/**
	 * The maximum number of results to return
	 *
	 * @var int
	 */
	protected $_maxResults = 500;

	/**
	 * Return Fields
	 *
	 * @var array
	 */
	protected $_returnFields = array();

	/**
	 * default value for all return fields
	 *
	 * @var array
	 */
	protected $_returnFieldsDefault = array('*','+');


	/**
	 * Constructor to create new LDAP object
	 *
	 */
	public function __construct()
	{}

	/**
	 * Sets the max number of results to be returned
	 *
	 * @param int $max
	 */
	public function setMaxNumResults($max)
	{
        $this->_maxResults = $max;
	}

	/**
	 * Adds a return field to the list of fields to get from LDAP
	 *
	 * @param string $field
	 */
	public function addReturnField($field)
	{
		if (!in_array($field, $this->_returnFields)) {
			$this->_returnFields[] = $field;
		}
	}

	/**
	 * Removes a field from the return list
	 *
	 * @param string $field
	 */
	public function removeReturnField($field)
	{
		$temp = array();
		foreach ($this->_returnFields as $s) {
			if ($s != $field) {
				$temp[] = $s;
			}
		}
		$this->_returnFields = $temp;
	}

	/**
	 * Resets the return fields to the default fields
	 *
	 */
	public function setReturnFieldsToDefault()
	{
		$this->_returnFields = $this->_returnFieldsDefault;
	}

	/**
	 * Connect to a given LDAP host with the qualifications stated
	 *
	 * @param $ldapHost - URL for the LDAP Server
	 * @param $ldapBindDn - LDAP directory bind
	 * @param $ldapPass - Password (not needed for anon. bind)
	 * @return mixed - true or error message
	 * @throws Ot_Resource_Exception on LDAP Error
	 */
	public function connect($ldapHost, $ldapBindDn, $ldapPass)
	{

	    $resource = null;

	    // Check to see if the resouce is already opened
	    if (isset($this->_resources[$ldapHost][$ldapBindDn]["resource"])) {
	        $resource = $this->_resources[$ldapHost][$ldapBindDn]["resource"];
	    }

	    // if there is a resource, return it
	    if (!is_null($resource)) {
	        $this->_link = $resource;
	    }

	    // Connect to the resource
		$resource = @ldap_connect($ldapHost);

		ldap_set_option($resource, LDAP_OPT_PROTOCOL_VERSION, 3) ;
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

		if (ldap_errno($resource) != 0) {
		    throw new Ot_Resource_Exception(ldap_error($resource), ldap_errno($resource));
		}

		if (!@ldap_bind($resource, $ldapBindDn, $ldapPass)) {
		    throw new Exception(ldap_error($resource), ldap_errno($resource));
		}

		// populate the link class variable with the new LDAP link resource
		$this->_link = $resource;

		$this->_resource[$ldapHost][$ldapBindDn]["resource"] = $resource;

		return true;
	}


	/**
	 * Runs a standard search on LDAP
	 *
	 * @param string $query_string
	 * @param string $context
	 * @return LDAP result set
	 * @throws Ot_Resource_Exception on LDAP Error
	 */
	public function search($query_string, $context)
	{
        // Make sure there are some return fields set
		if (count($this->_returnFields) == 0) {
			$this->setReturnFieldsToDefault();
		}

		// Do the search
		$ldap_result = @ldap_search($this->_link,
		                            $context,
		                            $query_string,
		                            $this->_returnFields,
		                            0,
		                            $this->_maxResults);

		if (!$ldap_result) {
		    throw new Exception(ldap_error($this->_link), ldap_errno($this->_link));
		}

		// Return empty array if there are no etries
		if (@ldap_count_entries($this->_link, $ldap_result) == 0) {
			return array();
		}

		return @ldap_get_entries($this->_link, $ldap_result);
	}
}

?>