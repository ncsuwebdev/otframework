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
 * @package    Ot_Language
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to show all languages
 *
 * @package    Ot_Language
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
final class Ot_Model_Language
{
        
    protected static $_languageList = array(
        'AA' => 'Afar',
        'AB' => 'Abkhazian',
        'AF' => 'Afrikaans',
        'AM' => 'Amharic',
        'AR' => 'Arabic',
        'AS' => 'Assamese',
        'AY' => 'Aymara',
        'AZ' => 'Azerbaijani',
        'BA' => 'Bashkir',
        'BE' => 'Byelorussian',
        'BG' => 'Bulgarian',
        'BH' => 'Bihari',
        'BI' => 'Bislama',
        'BN' => 'Bengali',
        'BO' => 'Tibetan',
        'BR' => 'Breton',
        'CA' => 'Catalan',
        'CO' => 'Corsican',
        'CS' => 'Czech',
        'CY' => 'Welsh',
        'DA' => 'Danish',
        'DE' => 'German',
        'DZ' => 'Bhutani',
        'EL' => 'Greek',
        'EN' => 'English',
        'EO' => 'Esperanto',
        'ES' => 'Spanish',
        'ET' => 'Estonian',
        'EU' => 'Basque',
        'FA' => 'Persian',
        'FI' => 'Finnish',
        'FJ' => 'Fiji',
        'FO' => 'Faeroese',
        'FR' => 'French',
        'FY' => 'Frisian',
        'GA' => 'Irish',
        'GD' => 'Gaelic',
        'GL' => 'Galician',
        'GN' => 'Guarani',
        'GU' => 'Gujarati',
        'HA' => 'Hausa',
        'HI' => 'Hindi',
        'HR' => 'Croatian',
        'HU' => 'Hungarian',
        'HY' => 'Armenian',
        'IA' => 'Interlingua',
        'IE' => 'Interlingue',
        'IK' => 'Inupiak',
        'IN' => 'Indonesian',
        'IS' => 'Icelandic',
        'IT' => 'Italian',
        'IW' => 'Hebrew',
        'JA' => 'Japanese',
        'JI' => 'Yiddish',
        'JW' => 'Javanese',
        'KA' => 'Georgian',
        'KK' => 'Kazakh',
        'KL' => 'Greenlandic',
        'KM' => 'Cambodian',
        'KN' => 'Kannada',
        'KO' => 'Korean',
        'KS' => 'Kashmiri',
        'KU' => 'Kurdish',
        'KY' => 'Kirghiz',
        'LA' => 'Latin',
        'LN' => 'Lingala',
        'LO' => 'Laothian',
        'LT' => 'Lithuanian',
        'LV' => 'Latvian',
        'MG' => 'Malagasy',
        'MI' => 'Maori',
        'MK' => 'Macedonian',
        'ML' => 'Malayalam',
        'MN' => 'Mongolian',
        'MO' => 'Moldavian',
        'MR' => 'Marathi',
        'MS' => 'Malay',
        'MT' => 'Maltese',
        'MY' => 'Burmese',
        'NA' => 'Nauru',
        'NE' => 'Nepali',
        'NL' => 'Dutch',
        'NO' => 'Norwegian',
        'OC' => 'Occitan',
        'OM' => 'Oromo',
        'OR' => 'Oriya',
        'PA' => 'Punjabi',
        'PL' => 'Polish',
        'PS' => 'Pashto',
        'PT' => 'Portuguese',
        'QU' => 'Quechua',
        'RM' => 'Rhaeto-Romance',
        'RN' => 'Kirundi',
        'RO' => 'Romanian',
        'RU' => 'Russian',
        'RW' => 'Kinyarwanda',
        'SA' => 'Sanskrit',
        'SD' => 'Sindhi',
        'SG' => 'Sangro',
        'SH' => 'Serbo-Croatian',
        'SI' => 'Singhalese',
        'SK' => 'Slovak',
        'SL' => 'Slovenian',
        'SM' => 'Samoan',
        'SN' => 'Shona',
        'SO' => 'Somali',
        'SQ' => 'Albanian',
        'SR' => 'Serbian',
        'SS' => 'Siswati',
        'ST' => 'Sesotho',
        'SU' => 'Sudanese',
        'SV' => 'Swedish',
        'SW' => 'Swahili',
        'TA' => 'Tamil',
        'TE' => 'Tegulu',
        'TG' => 'Tajik',
        'TH' => 'Thai',
        'TI' => 'Tigrinya',
        'TK' => 'Turkmen',
        'TL' => 'Tagalog',
        'TN' => 'Setswana',
        'TO' => 'Tonga',
        'TR' => 'Turkish',
        'TS' => 'Tsonga',
        'TT' => 'Tatar',
        'TW' => 'Twi',
        'UK' => 'Ukrainian',
        'UR' => 'Urdu',
        'UZ' => 'Uzbek',
        'VI' => 'Vietnamese',
        'VO' => 'Volapuk',
        'WO' => 'Wolof',
        'XH' => 'Xhosa',
        'YO' => 'Yoruba',
        'ZH' => 'Chinese',
        'ZU' => 'Zulu',
    );

    /**
     * Gets the timezone list
     *
     * @return array of timezones
     */
    public static function getLanguageList()
    {
        return self::$_languageList;
    }
    
    /**
     * Gets the language name based on its key
     *
     * @param string $key
     * @return unknown
     */
    public static function getLanguageName($key)
    {
        if (isset(self::$_languageList[strtoupper($key)])) {
            return self::$_languageList[strtoupper($key)];
        }
        return null;
    }
}