<?php

class Ot_Application_Version
{
    const APP_VERSION_REGISTRY_KEY = '_appVersion';

    public static function getVersion()
    {
        if (Zend_Registry::isRegistered(self::APP_VERSION_REGISTRY_KEY)) {
            return Zend_Registry::get(self::APP_VERSION_REGISTRY_KEY);
        }

        $cache = Zend_Registry::get('cache');

        if (!$version = $cache->load('Ot_Application_Version')) {

            $version = trim(file_get_contents(APPLICATION_PATH . '/../_version.txt'));

            if ($version == '') {
                $version = 'unknown';
            }

            Zend_Registry::set(self::APP_VERSION_REGISTRY_KEY, $version);

            $cache->save($version, 'Ot_Application_Version');
        }

        return $version;
    }
}