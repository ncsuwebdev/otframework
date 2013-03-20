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
 * @package    Ot_FrontController_Plugin_Language
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Language front controller plugin to provide i18n to our apps.
 *
 * @package    Ot_FrontController_Plugin_Language
 * @category   Front Controller Plugin
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_FrontController_Plugin_Language extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $locale = new Zend_Locale();
        
        $options = array(
            'scan' => Zend_Translate::LOCALE_FILENAME,
            'clear' => false,
            'disableNotices' => true
        );
                                    
        Zend_Translate::setCache(Zend_Registry::get('cache'));
        
        if (isset($_COOKIE['language_select'])) {
            $language = $_COOKIE['language_select'];
        } else {
            $language = 'en';
        }
        
        $translate = new Zend_Translate('csv', APPLICATION_PATH . '/languages', 'auto', $options);
        $translate->addTranslation(APPLICATION_PATH . '/languages/ot');
        $translate->addTranslation(APPLICATION_PATH . '/../overrides/languages');
        
        if (!$translate->isAvailable($language)) {
            throw new Exception('Language ' . $language . ' is not available');
        }
                                
        $locale->setLocale($language);
        $translate->setLocale($locale);
        
        Zend_Registry::set('Zend_Locale', $locale);
        Zend_Registry::set('Zend_Translate', $translate);                       
    }
}