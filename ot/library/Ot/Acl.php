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
 * @package    Ot_Acl
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manages all ACL's for the application.
 *
 * @package    Ot_Acl
 * @category   Access Control
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 */
class Ot_Acl extends Zend_Acl
{
	protected $_roles = null;
	
    /**
     * Creates a new instance of the ACL's
     *
     */
    public function __construct()
    {

    	//$xml = Zend_Registry::get('aclConfig');
    	
        $controllers = Zend_Controller_Front::getInstance()->getControllerDirectory();

        // gets all controllers to get the actions in them
        foreach ($controllers as $key => $value) {
            foreach (new DirectoryIterator($value) as $file) {
                if (preg_match('/controller\.php/i', $file)) {
                    $this->add(new Zend_Acl_Resource($key . '_' . strtolower(preg_replace('/controller\.php/i', '', $file))));  
                }
            }
        }
        
        $roles = $this->getAvailableRoles();
        
        foreach ($roles as $r) {
        	$this->addRole(new Zend_Acl_Role($r['name']), ($r['inherit'] != '') ? $r['inherit'] : null);
        	
        	foreach ($r['allows'] as $a) {
        		$this->allow($r['name'],
        		    ($a['resource'] == '*') ? null : $a['resource'], 
                    ($a['privilege'] == '*') ? null : $a['privilege']
                    );
        	}
        	
        	foreach ($r['denys'] as $d) {
        		$this->deny($r['name'],
                    ($d['resource'] == '*') ? null : $d['resource'], 
                    ($d['privilege'] == '*') ? null : $d['privilege']
                    );
        	}
        }
        
        $this->_roles = $roles;
    }

    public function getAvailableRoles($role = '')
    {
    	if (!is_null($this->_roles)) {
    		return $this->_roles;
    	}
    	
        $xml = Zend_Registry::get('aclConfig');
        
        $roles = array();
        foreach ($xml->roles->role as $x) {
            $temp = array();

            $temp['name']     = (string)$x->name;
            $temp['inherit']  = (string)$x->inherit;
            $temp['editable'] = ((int)$x->editable == 1);
            $temp['allows']   = array();
            $temp['denys']    = array();

            if ($x->allows instanceof Zend_Config) {
                if ($x->allows->allow->get('0')) {
                    foreach ($x->allows->allow as $a) {
                        $tempAllow = array();
    
                        $tempAllow['resource'] = (string)$a->resource;
                        $tempAllow['privilege'] = (string)$a->privilege;
    
                        $temp['allows'][] = $tempAllow;                        
                    }
                } else {
                    $tempAllow = array();
    
                    $tempAllow['resource'] = (string)$x->allows->allow->resource;
                    $tempAllow['privilege'] = (string)$x->allows->allow->privilege;
    
                    $temp['allows'][] = $tempAllow;
                }
            }
            
            if ($x->denys instanceof Zend_Config) {
                if ($x->denys->deny->get('0')) {
                    foreach ($x->denys->deny as $d) {
                        $tempDeny = array();
    
                        $tempDeny['resource'] = (string)$d->resource;
                        $tempDeny['privilege'] = (string)$d->privilege;
    
                        $temp['denys'][] = $tempDeny;
                    }
                } else {
                    $tempDeny = array();
    
                    $tempDeny['resource'] = (string)$x->denys->deny->resource;
                    $tempDeny['privilege'] = (string)$x->denys->deny->privilege;
    
                    $temp['denys'][] = $tempDeny;
                }
            }
            
            if ($role != '' && $role == $temp['name']) {
                return $temp;
            }

            $roles[] = $temp;
        }

        return $roles;
    }

    public function addCustomRole($data, $configFilePath)
    {
        if ($this->hasRole($data['name'])) {
            throw new Exception('Role already exists.  Can not create new role');
        }

        $this->_writeAclConfig($data, false, $configFilePath);
    }

    public function editCustomRole($data, $configFilePath)
    {
        $this->_writeAclConfig($data, false, $configFilePath);
    }

    public function deleteCustomRole($roleName, $configFilePath)
    {
        $this->_writeAclConfig(array('name' => $roleName), true, $configFilePath);
    }

    /**
     * This is a brother to isAllowed, but instead of returning false is a role
     * has access to all privleges in a resource, it will return true if and only
     * if the role has access to at least one privlege within the resource.
     *
     * @param string $role
     * @param string $resource
     * @param string $privilege
     * @return boolean
     */
    public function hasSomeAccess($role, $resource)
    {
        $access = $this->getResourcesWithSomeAccess($role);

        if ($access == "*") {
            return true;
        }

        return in_array($resource, $access);
    }

    public function getResourcesWithSomeAccess($role)
    {
        $rules = $this->_rules;

        if (isset($rules['allResources']['byRoleId'][$role]) && $rules['allResources']['byRoleId'][$role]['allPrivileges']['type'] === self::TYPE_ALLOW) {
            return '*';
        }

        $access = array();

        foreach ($rules['byResourceId'] as $resource => $privilege) {
            if (isset($privilege['byRoleId'][$role])) {
                if (isset($privilege['byRoleId'][$role]['allPrivileges']) && $privilege['byRoleId'][$role]['allPrivileges']['type'] === self::TYPE_ALLOW) {
                    $access[] = $resource;
                } else {
                    foreach ($privilege['byRoleId'][$role]['byPrivilegeId'] as $priv => $act) {
                        if ($act['type'] === self::TYPE_ALLOW) {
                            $access[] = $resource;
                            break;
                        }
                    }
                }
            }
        }

        $parents = $this->_getRoleRegistry()->getParents($role);

        if (count($parents) != 0) {

            foreach ($parents as $key => $value) {
                $access = array_unique(array_merge($access, $this->getResourcesWithSomeAccess($key)));
            }
        }

        return $access;
    }

    protected function _writeAclConfig($data, $remove, $configFilePath)
    {
    	if (!is_writable($configFilePath)) {
    		throw new Exception('Config file path (' . $configFilePath . ') is not writable');
    	}
    	
        $current = $this->getAvailableRoles();
        
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;

        $conf = $doc->createElement('production');
        $conf = $doc->appendChild($conf);
        
        $prod = $doc->createElement('production');
        $prod = $conf->appendChild($prod);
        
        $root = $doc->createElement('roles');
        $root = $prod->appendChild($root);

        if (!$this->hasRole($data['name'])) {
            $current = array_merge($current, array($data));
        }

        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
        $filter->addFilter(new Zend_Filter_StringToLower());
        
        $roles = array();
        foreach ($current as $r) {
            if ($r['name'] == $data['name']) {
                if ($remove) {
                    continue;
                }
                $r = $data;

                if (isset($r['newName'])) {
                    $r['name'] = $r['newName'];
                }
            }

            $role = $doc->createElement('role');
            $role = $root->appendChild($role);

            $name = $doc->createElement('name');
            $name = $role->appendChild($name);

            $nameValue = $doc->createTextNode($r['name']);
            $nameValue = $name->appendChild($nameValue);

            $inherit = $doc->createElement('inherit');
            $inherit = $role->appendChild($inherit);

            $inheritValue = $doc->createTextNode($r['inherit']);
            $inheritValue = $inherit->appendChild($inheritValue);

            $editable = $doc->createElement('editable');
            $editable = $role->appendChild($editable);

            $editableValue = $doc->createTextNode($r['editable']);
            $editableValue = $editable->appendChild($editableValue);

            $allows = $doc->createElement('allows');
            $allows = $role->appendChild($allows);

            foreach ($r['allows'] as $a) {
                $allow = $doc->createElement('allow');
                $allow = $allows->appendChild($allow);

                $resource = $doc->createElement('resource');
                $resource = $allow->appendChild($resource);

                $resourceValue = $doc->createTextNode($filter->filter($a['resource']));
                $resourceValue = $resource->appendChild($resourceValue);

                $privilege = $doc->createElement('privilege');
                $privilege = $allow->appendChild($privilege);

                $privilegeValue = $doc->createTextNode($filter->filter($a['privilege']));
                $privilegeValue = $privilege->appendChild($privilegeValue);
            }

            $denys = $doc->createElement('denys');
            $denys = $role->appendChild($denys);

            foreach ($r['denys'] as $d) {
                $deny = $doc->createElement('deny');
                $deny = $denys->appendChild($deny);

                $resource = $doc->createElement('resource');
                $resource = $deny->appendChild($resource);

                $resourceValue = $doc->createTextNode($filter->filter($d['resource']));
                $resourceValue = $resource->appendChild($resourceValue);

                $privilege = $doc->createElement('privilege');
                $privilege = $deny->appendChild($privilege);

                $privilegeValue = $doc->createTextNode($filter->filter($d['privilege']));
                $privilegeValue = $privilege->appendChild($privilegeValue);
            }

        }

        $doc->save($configFilePath);
    }
    

    /**
     * Gets all the children of a given role
     *
     * @param string $role
     * @param string $roles
     * @param array $children
     * @return array
     */
    public function getChildrenOfRole($role, $roles = '', $children = array())
    {
        if ($roles == '') {
            $roles = $this->getAvailableRoles();
        }

        foreach ($roles as $r) {
            if ($r['inherit'] == $role) {
                if (!isset($children[$r['name']])) {
                    $children[$r['name']] = array();
                }
                if (isset($children[$r['inherit']])) {
                    $children[$r['name']] = array_merge($children[$r['inherit']], array($role));
                } else {
                    $children[$r['name']][] = $role;
                }

                $children = $this->getChildrenOfRole($r['name'], $roles, $children);
            }
        }

        return $children;
    }


    /**
     * Gets all resources with permissions based on the passed role
     *
     * @param string $role
     * @return array
     */
    public function getResources($role = '')
    {
        $controllers = Zend_Controller_Front::getInstance()->getControllerDirectory();

        if (is_array($controllers)) {
            ksort($controllers);
        }

        $roles = $this->getAvailableRoles();

        $temp = array();

        // gets the role from teh acl, with all allows and denys set
        foreach ($roles as $r) {
            $temp[$r['name']] = $r;
            if ($role != '' && $r['name'] == $role) {
                $role = $r;
                break;
            }
        }

        // Sets the denys for the role
        $denys = array();
        if (isset($role['denys'])) {
            foreach ($role['denys'] as $d) {
                $denys[$d['resource']] = $d['privilege'];
            }
        }

        $roles = $temp;

        $result = array();
        
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
        $filter->addFilter(new Zend_Filter_StringToLower());        
        
        // gets all controllers to get the actions in them
        foreach ($controllers as $key => $value) {
            foreach (new DirectoryIterator($value) as $file) {
                if (preg_match('/controller\.php/i', $file)) {

                    if ($key == 'default') {
                        $classname = preg_replace('/\.php/i', '', $file);
                    } else {
                        $classname = ucwords(strtolower($key)) . '_' .
                            preg_replace('/\.php/i', '', $file);
                    }

                    $controllerName = preg_replace('/^[^_]*\_/', '',
                        preg_replace('/controller/i', '', $classname));

                    $controllerName = $filter->filter($controllerName);
                    
                    $resource = strtolower($key . '_' . $controllerName);

                    $result[$key][$controllerName]['all'] = array('access' => false, 'inherit' => '');

                    $noInheritance = false;
                    $inherit = (isset($role['name'])) ? $role['name'] : '';

                    $allows = array();
                    while (!$noInheritance) {

                        $iAllows = array();
                        $iDenys  = array();

                        if (isset($roles[$inherit]['allows'])) {
                            foreach ($roles[$inherit]['allows'] as $a) {
                                $allows[$a['resource']] = $a['privilege'];
                                $iAllows[$a['resource']] = $a['privilege'];
                            }
                        }

                        if (isset($roles[$inherit]['denys'])) {
                            foreach ($roles[$inherit]['denys'] as $a) {
                                $iDenys[$a['resource']] = $a['privilege'];
                            }
                        }

                        // Checks to see if the inheriting role allows the rource
                        if (in_array('*', array_keys($allows)) || (isset($allows[$resource]) && $allows[$resource] == '*')) {

                            // checks to see that even though the inheriting role allows the resource that the role in question doesnt specifically deny it
                            if (!(isset($denys[$resource]) && $denys[$resource] == '*')) {
                                $result[$key][$controllerName]['all']['access'] = true;
                                if (isset($iAllows[$resource]) && $iAllows[$resource] == '*') {
                                    $result[$key][$controllerName]['all']['inherit'] = $inherit;
                                }
                            }
                        }

                        if (isset($roles[$inherit]['inherit']) && $roles[$inherit]['inherit'] != '') {
                            $inherit = $roles[$inherit]['inherit'];
                        } else {
                            $noInheritance = true;
                        }
                    }

                    require_once $controllers[$key] . DIRECTORY_SEPARATOR . $file;

                    $class = new ReflectionClass($classname);
                    $methods = $class->getMethods();

                    $result[$key][$controllerName]['description'] = $this->_getDescriptionFromCommentBlock($class->getDocComment());
                    
                    foreach ($methods as $m) {
                        if (preg_match('/action/i', $m->name) &&
                            basename($class->getMethod($m->name)->getFileName()) == $file) {

                            $action = $filter->filter(preg_replace('/action/i', '', $m->name));
                            
                            if ($role != '') {
                                $result[$key][$controllerName]['part'][$action]['access'] = $this->isAllowed($role['name'], $resource, $action);
                            } else {
                                $result[$key][$controllerName]['part'][$action]['access'] = false;
                            }
                            
                            $result[$key][$controllerName]['part'][$action]['description'] = $this->_getDescriptionFromCommentBlock($m->getDocComment());

                            $noInheritance = (isset($role['inherit']) && $role['inherit'] == '');
                            $inherit = (isset($role['inherit'])) ? $role['inherit'] : '';

                            $result[$key][$controllerName]['part'][$action]['inherit'] = '';

                            while (!$noInheritance) {
                                $iAllows = array();
                                $iDenys  = array();

                                if (isset($roles[$inherit]['allows'])) {
                                    foreach ($roles[$inherit]['allows'] as $a) {
                                        $iAllows[] = $a['resource'] . '_' . $a['privilege'];
                                    }
                                }

                                if (isset($roles[$inherit]['denys'])) {
                                    foreach ($roles[$inherit]['denys'] as $a) {
                                        $iDenys[] = $a['resource'] . '_' . $a['privilege'];
                                    }
                                }

                                if ($result[$key][$controllerName]['part'][$action]['access'] == false) {
                                    if (in_array($resource . '_' . $action, $iDenys) && $result[$key][$controllerName]['part'][$action]['inherit'] == '') {
                                        $result[$key][$controllerName]['part'][$action]['inherit'] = $inherit;
                                    }
                                } else {
                                    if (in_array($resource . '_' . $action, $iAllows) && $result[$key][$controllerName]['part'][$action]['inherit'] == '') {
                                        $result[$key][$controllerName]['part'][$action]['inherit'] = $inherit;
                                    }
                                }

                                if (isset($roles[$inherit]['inherit']) && $roles[$inherit]['inherit'] != '') {
                                    $inherit = $roles[$inherit]['inherit'];
                                } else {
                                    $noInheritance = true;
                                }
                            }
                        }
                    }


                    if (is_array($result[$key])) {
                        ksort($result[$key]);
                    }

                    if (is_array($result[$key][$controllerName]['part'])) {
                        ksort($result[$key][$controllerName]['part']);
                    }
                }
            }
        }

        return $result;
    }
    
    protected function _getDescriptionFromCommentBlock($str)
    {
        $str = preg_replace('/@[^\n]*/', '', $str);
        $str = preg_replace('/\s*\*\s/', '', $str);
        $str = preg_replace('/(\/\*|\*\/)*/', '', $str);
        
        return trim($str);
    }    
}