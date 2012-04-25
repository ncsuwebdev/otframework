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
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Manages all ACL's for the application.
 *
 * @package    Ot_Acl
 * @category   Access Control
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 */
class Ot_Acl extends Zend_Acl
{
    protected $_roles = null;

    /**
     * Creates a new instance of the ACL's
     *
     */
    public function __construct($scope = 'application')
    {

        if ($scope == 'application') {
            $controllers = Zend_Controller_Front::getInstance()->getControllerDirectory();

            // Gets all controllers to get the actions in them
            foreach ($controllers as $key => $value) {
                foreach (new DirectoryIterator($value) as $file) {
                    if (preg_match('/controller\.php/i', $file)) {
                        $this->add(
                            new Zend_Acl_Resource(
                                $key . '_' . strtolower(preg_replace('/controller\.php/i', '', $file))
                            )
                        );
                    }
                }
            }
        } elseif ($scope == 'remote') {
            
            $register = new Ot_Api_Register();
            $endpoints = $register->getApiEndpoints();
            
            foreach ($endpoints as $endpoint) {
                $this->add(new Zend_Acl_Resource('api_' . strtolower($endpoint->getName())));
            }   
        }

        $roles = $this->getAvailableRoles('', $scope);
        $notAdded = $roles;

        foreach ($notAdded as &$r) {
            try {
                $this->addRole(new Zend_Acl_Role($r['roleId']), ($r['inheritRoleId'] != 0) ? $r['inheritRoleId'] : null);

                foreach ($r['rules'] as $rule) {
                    if ($rule['resource'] == '*' || $this->has($rule['resource'])) {
                        $this->{$rule['type']}($r['roleId'],
                            ($rule['resource'] == '*') ? null : $rule['resource'],
                            ($rule['privilege'] == '*') ? null : $rule['privilege']
                        );
                    }
                }
            } catch (Zend_Acl_Role_Registry_Exception $e) {
                $notAdded[] = &$r;
            }
        }

        unset($r);

        $this->_roles = $roles;
    }

    public function getAvailableRoles($role = '', $scope = 'application')
    {
        if (!is_null($this->_roles)) {
            return $this->_roles;
        }

        $role = new Ot_Model_DbTable_Role();
        return $role->getRoles($scope);
    }

    /**
     * Gets all the children of a given role
     *
     * @param string $role
     * @param string $roles
     * @param array $children
     * @return array
     */
    public function getChildrenOfRole($roleId, $roles = '', $children = array())
    {
        if ($roles == '') {
            $roles = $this->getAvailableRoles();
        }

        foreach ($roles as &$r) {
            unset($r['rules']);
        }
        unset($r);

        foreach ($roles as $r) {
            if ($r['inheritRoleId'] == $roleId) {
                if (!isset($children[$r['roleId']])) {
                    $children[$r['roleId']] = $roles[$r['roleId']];
                    $children[$r['roleId']]['parent'] = array();
                }

                if (isset($children[$r['inheritRoleId']])) {
                    $children[$r['roleId']]['parent'] = array_merge($children[$r['roleId']]['parent'], $roles[$roleId]);
                } else {
                    $children[$r['roleId']]['parent'] = $roles[$roleId];
                }

                $children = $this->getChildrenOfRole($r['roleId'], $roles, $children);
            }
        }

        return $children;
    }

    public function getRemoteResources($roleId = 0)
    {
        $roles = $this->getAvailableRoles();

        $role = 0;

        if ($roleId != 0) {
            if (!isset($roles[$roleId])) {
                throw new Ot_Exception('Requested role not found in the access list.');
            }

            $role = $roles[$roleId];
        }

        // Sets the denys for the role
        $denys = array();
        if (isset($role['rules'])) {
            foreach ($role['rules'] as $rule) {
                if ($rule['type'] == 'deny') {
                    $denys[$rule['resource']] = $rule['privilege'];
                }
            }
        }

        $result = array();

        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
        $filter->addFilter(new Zend_Filter_StringToLower());

        $register = new Ot_Api_Register();
        $endpoints = $register->getApiEndpoints();

        // the Api $key is really kind of a "fake" key in that the Api module
        // doesn't exist...it's simply a placeholder
        $key = "api";

        foreach ($endpoints as $endpoint) {
            
            $controllerName = $endpoint->getName();

            $resource = strtolower($key . '_' . $controllerName);
            //$resource = strtolower($controllerName);

            $result[$key][$controllerName]['all'] = array('access' => false, 'inheritRoleId' => '');

            $noInheritance = false;
            $inherit = $roleId;

            $allows = array();
            while (!$noInheritance) {

                $iAllows = array();
                $iDenys  = array();

                if (isset($roles[$inherit]['rules'])) {
                    foreach ($roles[$inherit]['rules'] as $rule) {
                        if ($rule['type'] == 'allow') {
                            $allows[$rule['resource']] = $rule['privilege'];
                            $iAllows[$rule['resource']] = $rule['privilege'];
                        } else {
                            $iDenys[$rule['resource']] = $rule['privilege'];
                        }
                    }
                }

                // Checks to see if the inheriting role allows the rource
                if (in_array('*', array_keys($allows)) || (isset($allows[$resource]) && $allows[$resource] == '*')) {

                    /* Checks to see that even though the inheriting role allows the resource that the role in
                     * question doesnt specifically deny it.
                     */
                    if (!(isset($denys[$resource]) && $denys[$resource] == '*')) {
                        $result[$key][$controllerName]['all']['access'] = true;
                        if (isset($iAllows[$resource]) && $iAllows[$resource] == '*') {
                            $result[$key][$controllerName]['all']['inheritRoleId'] = $inherit;
                        }
                    }
                }

                if (isset($roles[$inherit]['inheritRoleId']) && $roles[$inherit]['inheritRoleId'] != 0) {
                    $inherit = $roles[$inherit]['inheritRoleId'];
                } else {
                    $noInheritance = true;
                }
            }

            $result[$key][$controllerName]['description'] = "API Docs";
            if (!isset($result[$key][$controllerName]['part'])) {
                $result[$key][$controllerName]['part'] = array();
            }
            
            $methods = array('get', 'put', 'post', 'delete');
        
            foreach ($methods as $action) {
                
                if ($role != '') {
                    $holdingVar2 = $this->isAllowed($role['roleId'], $resource, $action);
                    $result[$key][$controllerName]['part'][$action]['access'] = $holdingVar2;
                } else {
                    $result[$key][$controllerName]['part'][$action]['access'] = false;
                }

                $holdingVar3 = strtoupper($action) . ' method for ' . $resource;
                $result[$key][$controllerName]['part'][$action]['description'] = $holdingVar3;

                $noInheritance = (isset($role['inheritRoleId']) && $role['inheritRoleId'] == 0);
                $inherit = (isset($role['inheritRoleId'])) ? $role['inheritRoleId'] : '';

                $result[$key][$controllerName]['part'][$action]['inheritRoleId'] = 0;

                while (!$noInheritance) {
                    
                    $iAllows = array();
                    $iDenys  = array();

                    if (isset($roles[$inherit]['rules'])) {
                        foreach ($roles[$inherit]['rules'] as $rule) {
                            if ($rule['type'] == 'allow') {
                                $iAllows[] = $rule['resource'] . '_' . $rule['privilege'];
                            } else {
                                $iDenys[] = $rule['resource'] . '_' . $rule['privilege'];
                            }
                        }
                    }

                    if ($result[$key][$controllerName]['part'][$action]['access'] == false) {
                        if (in_array($resource . '_' . $action, $iDenys)
                            && $result[$key][$controllerName]['part'][$action]['inheritRoleId'] == 0
                        ) {
                            $result[$key][$controllerName]['part'][$action]['inheritRoleId'] = $inherit;
                        }
                    } else {
                        if (in_array($resource . '_' . $action, $iAllows)
                            && $result[$key][$controllerName]['part'][$action]['inheritRoleId'] == 0
                        ) {
                            $result[$key][$resource]['part'][$action]['inheritRoleId'] = $inherit;
                        }
                    }

                    if (isset($roles[$inherit]['inheritRoleId'])
                        && $roles[$inherit]['inheritRoleId'] != 0
                    ) {
                        $inherit = $roles[$inherit]['inheritRoleId'];
                    } else {
                        $noInheritance = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Gets all resources with permissions based on the passed role
     *
     * @param string $role
     * @return array
     */
    public function getResources($roleId = 0)
    {
        $controllers = Zend_Controller_Front::getInstance()->getControllerDirectory();

        if (is_array($controllers)) {
            ksort($controllers);
        }

        $roles = $this->getAvailableRoles();

        $role = 0;

        if ($roleId != 0) {
            if (!isset($roles[$roleId])) {
                throw new Ot_Exception('Requested role not found in the access list.');
            }

            $role = $roles[$roleId];
        }

        // Sets the denys for the role
        $denys = array();
        if (isset($role['rules'])) {
            foreach ($role['rules'] as $rule) {
                if ($rule['type'] == 'deny') {
                    $denys[$rule['resource']] = $rule['privilege'];
                }
            }
        }

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
                        $classname = ucwords(strtolower($key)) . '_' . preg_replace('/\.php/i', '', $file);
                    }

                    $controllerName = preg_replace('/^[^_]*\_/', '', preg_replace('/controller/i', '', $classname));

                    $controllerName = $filter->filter($controllerName);

                    $resource = strtolower($key . '_' . $controllerName);

                    $result[$key][$controllerName]['all'] = array('access' => false, 'inheritRoleId' => '');

                    $noInheritance = false;
                    $inherit = $roleId;

                    $allows = array();
                    while (!$noInheritance) {

                        $iAllows = array();
                        $iDenys  = array();

                        if (isset($roles[$inherit]['rules'])) {
                            foreach ($roles[$inherit]['rules'] as $rule) {
                                if ($rule['type'] == 'allow') {
                                    $allows[$rule['resource']] = $rule['privilege'];
                                    $iAllows[$rule['resource']] = $rule['privilege'];
                                } else {
                                    $iDenys[$rule['resource']] = $rule['privilege'];
                                }
                            }
                        }

                        // Checks to see if the inheriting role allows the rource
                        if (in_array('*', array_keys($allows))
                            || (isset($allows[$resource]) && $allows[$resource] == '*')
                        ) {

                            /* Checks to see that even though the inheriting role allows the resource that the role in
                             * question doesnt specifically deny it.
                             */
                            if (!(isset($denys[$resource]) && $denys[$resource] == '*')) {
                                $result[$key][$controllerName]['all']['access'] = true;
                                if (isset($iAllows[$resource]) && $iAllows[$resource] == '*') {
                                    $result[$key][$controllerName]['all']['inheritRoleId'] = $inherit;
                                }
                            }
                        }

                        if (isset($roles[$inherit]['inheritRoleId']) && $roles[$inherit]['inheritRoleId'] != 0) {
                            $inherit = $roles[$inherit]['inheritRoleId'];
                        } else {
                            $noInheritance = true;
                        }
                    }

                    require_once $controllers[$key] . DIRECTORY_SEPARATOR . $file;

                    $class = new ReflectionClass($classname);
                    $methods = $class->getMethods();

                    $holdingVar1 = $this->_getDescriptionFromCommentBlock($class->getDocComment());
                    $result[$key][$controllerName]['description'] = $holdingVar1;
                    if (!isset($result[$key][$controllerName]['part'])) {
                        $result[$key][$controllerName]['part'] = array();
                    }

                    foreach ($methods as $m) {
                        if (preg_match('/action/i', $m->name)) {

                            $action = $filter->filter(preg_replace('/action/i', '', $m->name));

                            if ($role != '') {
                                $holdingVar2 = $this->isAllowed($role['roleId'], $resource, $action);
                                $result[$key][$controllerName]['part'][$action]['access'] = $holdingVar2;
                            } else {
                                $result[$key][$controllerName]['part'][$action]['access'] = false;
                            }

                            $holdingVar3 = $this->_getDescriptionFromCommentBlock($m->getDocComment());
                            $result[$key][$controllerName]['part'][$action]['description'] = $holdingVar3;

                            $noInheritance = (isset($role['inheritRoleId']) && $role['inheritRoleId'] == 0);
                            $inherit = (isset($role['inheritRoleId'])) ? $role['inheritRoleId'] : '';

                            $result[$key][$controllerName]['part'][$action]['inheritRoleId'] = 0;

                            while (!$noInheritance) {
                                $iAllows = array();
                                $iDenys  = array();

                                if (isset($roles[$inherit]['rules'])) {
                                    foreach ($roles[$inherit]['rules'] as $rule) {
                                        if ($rule['type'] == 'allow') {
                                            $iAllows[] = $rule['resource'] . '_' . $rule['privilege'];
                                        } else {
                                            $iDenys[] = $rule['resource'] . '_' . $rule['privilege'];
                                        }
                                    }
                                }

                                if ($result[$key][$controllerName]['part'][$action]['access'] == false) {
                                    if (in_array($resource . '_' . $action, $iDenys)
                                        && $result[$key][$controllerName]['part'][$action]['inheritRoleId'] == 0
                                    ) {
                                        $result[$key][$controllerName]['part'][$action]['inheritRoleId'] = $inherit;
                                    }
                                } else {
                                    if (in_array($resource . '_' . $action, $iAllows)
                                        && $result[$key][$controllerName]['part'][$action]['inheritRoleId'] == 0
                                    ) {
                                        $result[$key][$controllerName]['part'][$action]['inheritRoleId'] = $inherit;
                                    }
                                }

                                if (isset($roles[$inherit]['inheritRoleId'])
                                    && $roles[$inherit]['inheritRoleId'] != 0
                                ) {
                                    $inherit = $roles[$inherit]['inheritRoleId'];
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