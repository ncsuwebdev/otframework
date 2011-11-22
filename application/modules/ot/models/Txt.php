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
 * @package    Ot_Txt
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to house all txt message carriers
 *
 * @package    Ot_Txt
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
final class Ot_Model_Txt
{
    protected static $_carrierList = array(
        'alltel' => array(
            'display' => 'Alltel',
            'address' => 'message.alltel.com',
            'limit'   => '116',
        ),
        'att' => array(
            'display' => 'AT&T',
            'address' => 'txt.att.net',
            'limit'   => '160',
        ),
        'bellsouth' => array(
            'display' => 'Bell South Mobility',
            'address' => 'blsdcs.net',
            'limit'   => '160',
        ),
        'boost' => array(
            'display' => 'Boost Mobile',
            'address' => 'myboostmobile.com',
            'limit'   => '120',
        ),
        'edge' => array(
            'display' => 'Edge Wireless',
            'address' => 'sms.edgewireless.com',
            'limit'   => '160',
        ),
        'nextel' => array(
            'display' => 'NexTel',
            'address' => 'messaging.nextel.com',
            'limit'   => '126',
        ),
        'qwest' => array(
            'display' => 'Quest',
            'address' => 'qwestmp.com',
            'limit'   => '100',
        ),
        'sprint' => array(
            'display' => 'Sprint PCS',
            'address' => 'messaging.sprintpcs.com',
            'limit'   => '160',
        ),
        'suncom' => array(
            'display' => 'Suncom',
            'address' => 'tms.suncom.com',
            'limit'   => '110',
        ),
        'tmobile' => array(
            'display' => 'T-Mobile',
            'address' => 'tmomail.net',
            'limit'   => '160',
        ),
        'uscellular' => array(
            'display' => 'US Cellular',
            'address' => 'email.uscc.net',
            'limit'   => '150',
        ),
        'verizon' => array(
            'display' => 'Verizon Wireless',
            'address' => 'vtext.com',
            'limit'   => '140',
        ),
        'virgin' => array(
            'display' => 'Virgin Mobile',
            'address' => 'vmobl.com',
            'limit'   => '160',
        ),
        'unicel' => array(
            'display' => 'Unicel',
            'address' => 'utext.com',
            'limit' => '140',
        ),
    );

    /**
     * Gets the carrier requested.  If empty, returns the entire list.
     *
     * @return array
     */
    public static function getCarrier($carrier = null)
    {
        if (!is_null($carrier)) {
            return (isset(self::$_carrierList[$carrier])) ? self::$_carrierList[$carrier] : null;
        }
        
        return self::$_carrierList;
    }
    
}