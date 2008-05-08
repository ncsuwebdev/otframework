<?php

/**
 * PHP 5 only
 *
 * This Remedy XML RPC client module allows for easy interaction with the perl
 * ARS XML RPC server.
 *
 * @category  Ot
 * @package   Remedy
 * @author    Garrison Locke
 * @copyright 2007 NC State University Information Technology Division - IT App Dev
 *
 */
class Ot_Remedy {

    /**
     * config data for Remedy
     *
     */
    private $_config;

    /**
     * XML RPC server
     *
     */
    private $_url;

    /**
     * The HTTP client that will be used for the XML RPC calls
     *
     */
    protected $_httpClient;

    /**
     * The XML RPC client that is used to make the XML RPC calls
     *
     */
    protected $_client;


    public function __construct($hostname, $username, $password)
    {
        require_once 'Zend/Http/Client.php';
        require_once 'Zend/XmlRpc/Client.php';

        $this->_config = array(
            'hostname' => $hostname,
            'username' => $username,
            'password' => $password
        );

        $this->_url = "https://itdmw00.unity.ncsu.edu:443/cgi-bin/arsxmlrpc/server.pl";

        try {
            $httpClientConfig = array('ssltransport' => 'ssl');
            $this->_httpClient = new Zend_Http_Client($this->_url, $httpClientConfig);
            $this->_httpClient->setMethod(Zend_Http_Client::GET);
        } catch (Exception $e) {
            throw new Exception("Error creating HTTP client: " . $e->getMessage());
        }
        try {
            $this->_client = new Zend_XmlRpc_Client($this->_url, $this->_httpClient);

        } catch (Exception $e) {
            throw new Exception("Error creating XML RPC client: " . $e->getMessage());
        }
    }

    /**
     * Get a call by it's call number
     *
     * @param string $callId
     * @return The call
     */
    public function getCall($callId)
    {
        try {
            return $this->_client->call('arsxmlrpc.getCall', array($this->_config, $callId));
        } catch (Exception $e) {
            throw new Exception("Error getting call: " . $e->getMessage());
        }
    }


    /**
     * Insert a new call
     *
     * Valid keys are:
     *    'customer'  => string: realm id of the customer
     *    'agent'     => string: realm id of the agent
     *    'owner'     => string: realm id of the owner
     *    'workgroup' => string: name of the workgroup the call is in
     *    'action'    => string: action text
     *    'status'    => string: status of the call (see remedy)
     *    'priority'  => string: The priority of the call (see remedy)
     *    'origin'    => string: The origin of the call (see remedy)
     *    'impact'    => string: The impact of the call (see remedy)
     *    'resolved_dt' => int: UNIX EPOCH resolved date/time
     *    'contact_dt'  => int: UNIX EPOCH next contact date/time
     *    'product_id'  => string: id of product
     *    'solution_id' => string: id of solution
     *    'email_to'    => string: Sets the recipient email address
     *    'email_cc'    => string: Sets the carbon copy email address
     *    'problem_description' => string: description of the problem
     *    'problem_text'        => string: text to *ADD* to prob diary
     *    'history_text'        => string: text to *ADD* to history
     *
     * @param $call A keyed array of remedy call data
	 * @return The call id of the call that was inserted or false on error
     */
    public function insertCall($call)
    {

        try {
            return $this->_client->call('arsxmlrpc.insertCall', array($this->_config, $call));
        } catch (Exception $e) {
            throw new Exception("Error inserting new call: " . $e->getMessage());
        }
    }


    /**
     * Updates an existing call
     *
     * The key, 'id' is required for updating a call.
     *
     * Valid keys are:
     *    'customer'  => string: realm id of the customer
     *    'agent'     => string: realm id of the agent
     *    'owner'     => string: realm id of the owner
     *    'workgroup' => string: name of the workgroup the call is in
     *    'action'    => string: action text
     *    'status'    => string: status of the call (see remedy)
     *    'priority'  => string: The priority of the call (see remedy)
     *    'origin'    => string: The origin of the call (see remedy)
     *    'impact'    => string: The impact of the call (see remedy)
     *    'resolved_dt' => int: UNIX EPOCH resolved date/time
     *    'contact_dt'  => int: UNIX EPOCH next contact date/time
     *    'product_id'  => string: id of product
     *    'solution_id' => string: id of solution
     *    'email_to'    => string: Sets the recipient email address
     *    'email_cc'    => string: Sets the carbon copy email address
     *    'problem_description' => string: description of the problem
     *    'problem_text'        => string: text to *ADD* to prob diary
     *    'history_text'        => string: text to *ADD* to history
     *
     * @param $call Only keys that have been set in the array will be updated
	 * @return true on success else false on error
     */
    public function updateCall($call)
    {

        try {
            return $this->_client->call('arsxmlrpc.updateCall', array($this->_config, $call));
        } catch (Exception $e) {
            throw new Exception("Error updating call: " . $e->getMessage());
        }
    }


    /**
	 * Returns the attachment for the specified attachment ID
	 *
	 * @param $attachmentId The id of the attachment you want
	 * @return The attachment
	 */
	public function getAttachment($attachmentId){

		try {
		    return $this->_client->call('arsxmlrpc.getAttachment', array($this->_config, $attachmentId));
		} catch (Exception $e) {
		    throw new Exception("Error getting attachment: " . $e->getMessage());
		}
	}


	/**
	 * Gets a quick call from the system
	 *
	 * @param $callId The quick call id associated with the quick call to get
	 * @return An array of the quick call data
	 */
	public function getQuickCall($callId){

		try {
		    return $this->_client->call('arsxmlrpc.getQuickCall', array($this->_config, $callId));
		} catch (Exception $e) {
		    throw new Exception("Error getting quick call: " . $e->getMessage());
		}
	}


	/**
	 * Get all the quick calls in the system
	 *
	 * @return An array of all the quick calls
	 */
	public function searchQuickCalls(){

		try {
		    return $this->_client->call('arsxmlrpc.searchQuickCalls', array($this->_config));
		} catch (Exception $e) {
		    throw new Exception("Error getting all the quick calls: " . $e->getMessage());
		}
	}


	/**
	 * Gets a solution from the system
	 *
	 * @param $solutionId The solution to get
	 * @return The solution data
	 */
	public function getSolution($solutionId){

	    try {
		    return $this->_client->call('arsxmlrpc.getSolution', array($this->_config, $solutionId));
		} catch (Exception $e) {
		    throw new Exception("Error getting solution: " . $e->getMessage());
		}
	}


	/**
	 * Searches for calls based on the specified qualifier
	 *
	 * @param $qualifier A query string of the type generated by the advanced search in the Remedy client
	 * @return The array of results data
	 */
	public function searchCalls($qualifier){

		try {
		    return $this->_client->call('arsxmlrpc.searchCalls', array($this->_config, $qualifier));
		} catch (Exception $e) {
		    throw new Exception("Error searching the calls: " . $e->getMessage());
		}
	}


	/**
	 * Searches for solutions based on the specified qualifier
	 *
	 * @param $qualifier A query string of the type enerated by the advanced search box in the Remedy client
	 * @return The array of results data
	 */
	public function searchSolutions($qualifier){

	    try {
		    return $this->_client->call('arsxmlrpc.searchSolutions', array($this->_config, $qualifier));
		} catch (Exception $e) {
		    throw new Exception("Error searching the solutions: " . $e->getMessage());
		}
	}


    /**
	 * Searches for the top n solutions based on the wwwused field within the ARS.
	 *
	 * @param $limit The max number of solutions to return
	 * @return An array of results. The lower the index, the higher the usage of the solution.
	 */
	public function getTopSolutions($limit){

	    try {
		    return $this->_client->call('arsxmlrpc.getTopSolutions', array($this->_config, $limit));
		} catch (Exception $e) {
		    throw new Exception("Error getting top solutions: " . $e->getMessage());
		}
	}


	/**
	 * Increments the counter
	 *
	 * @param string $counterName
	 * @param string $unitId
	 * @return true or Exception
	 */
	public function incrementCounter($counterName, $unitId){

	    try {
		    return $this->_client->call('arsxmlrpc.incrementCounter', array($this->_config, $counterName, $unitId));
		} catch (Exception $e) {
		    throw new Exception("Error incrementing counter: " . $e->getMessage());
		}
	}

	/**
	 * Validates the ARS login credentials that were passed into the RPC server.
	 *
	 * @return true if the credentials are valid, null if they
	 *         are not valid, or Exception if an error occurs.
	 */
	public function validateCredentials(){

	    try {
		    $result = $this->_client->call('arsxmlrpc.validateCredentials', array($this->_config));
		} catch (Exception $e) {
		    throw new Exception("Error validating credentials: " . $e->getMessage());
		}

		if($result == 1){
			return true;
		}

		return null;
	}


	/**
	 * Gets user data from the ARS system
	 *
	 * @param $username The username of an ARS user to get data for
	 * @return The array user data
	 */
	public function getUser($username){

	    try {
		    return $this->_client->call('arsxmlrpc.getUser', array($this->_config, $username));
		} catch (Exception $e) {
		    throw new Exception("Error getting user data: " . $e->getMessage());
		}
	}


    /**
     * Gets the users in a workgroup
     *
     * @param string $workgroup
     * @return The array of results
     */
	public function getWorkgroupUsers($workgroup){

	    try {
		    return $this->_client->call('arsxmlrpc.getWorkgroupUsers', array($this->_config, $workgroup));
		} catch (Exception $e) {
		    throw new Exception("Error getting users in workgroup: " . $e->getMessage());
		}
	}


    /**
     * Gets group data from the system
     *
     * @param $groupname The groupname of an ARS group to get data for
     * @return The array of group data
     */
    public function getGroup($groupname){

        try {
		    return $this->_client->call('arsxmlrpc.getGroup', array($this->_config, $groupname));
		} catch (Exception $e) {
		    throw new Exception("Error getting group data: " . $e->getMessage());
		}
    }


    /**
	 * Gets a menu from the menu cache files.  We use the cache because getting
	 * a menu directly from the server is really slow.
     *
     * @param $name The name of the menu to get
	 * @return An array containing the menu if successful, else false on error
	 */
	function getMenu($name){

	    try {
		    return $this->_client->call('arsxmlrpc.getMenu', array($this->_config, $name));
		} catch (Exception $e) {
		    throw new Exception("Error getting menu data: " . $e->getMessage());
		}
	}
}