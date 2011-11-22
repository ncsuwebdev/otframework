<?php

/**
* Static class for countries and regions according to
* 
* https://www.cia.gov/library/publications/download 
*/

final class Ot_Model_Country
{
        
    protected static $_regionList = array(
    
        'africa' => 'Africa',
        'antartica' => 'Antartica',
        'australiaAndThePacific' => 'Australia and the Pacific',
        'centralAmericaAndTheCaribbean' => 'Central America and the Caribbean',
        'centralAsia' => 'Central Asia',
        'eastAndSoutheastAsia' => 'East and Southeast Asia',
        'europe' => 'Europe',
        'middleEast' => 'Middle East',
        'northAmerica' => 'North America',
        'southAmerica' => 'South America',
        'southAsia' => 'South Asia'
    );
    
    protected static $_countriesByRegionList = array(
        
        'africa' => array(
            'Algeria',
            'Angola',
            'Benin',
            'Botswana',
            'Burkina Faso',
            'Burundi',
            'Cameroon',
            'Cape Verde',
            'Central African Republic',
            'Chad',
            'Comoros',
            'Congo, Democratic Republic of the',
            'Congo, Republic of the',
            'Cote d\'Ivoire',
            'Djibouti',
            'Egypt',
            'Equatorial Guinea',
            'Eritrea',
            'Ethiopia',
            'Gabon',
            'Gambia, The',
            'Ghana',
            'Guinea',
            'Guinea-Bissau',
            'Kenya',
            'Lesotho',
            'Liberia',
            'Madagascar',
            'Malawi',
            'Mali',
            'Mauritania',
            'Mauritius',
            'Mayotte',
            'Morocco',
            'Mozambique',
            'Namibia',
            'Niger',
            'Nigeria',
            'Rwanda',
            'Saint Helena, Acension and Tristan da Cunha',
            'Sao Tome and Principe',
            'Senegal',
            'Seychelles',
            'Sierra Leone',
            'Somalia',
            'South Africa',
            'Swaziland',
            'Tanzania',
            'Togo',
            'Tunisia',
            'Uganda',
            'Western Sahara',
            'Zambia',
            'Zimbabwe',
        ),
        'antartica' => array(
            'Antarctica',
            'Bouvet Island',
            'French Southern and Antarctic Lands',
            'Heard Island and McDonald Island',
        ),
        'australiaAndThePacific' => array(
            'American Samoa',
            'Ashmore and Cartier Islands',
            'Australia',
            'Baker Island',
            'Christmas Island',
            'Cocos (Keeling) Islands',
            'Cook Islands',
            'Coral Sea Islands',
            'Fiji',
            'French Polynesia',
            'Guam',
            'Howland Island',
            'Jarvis Island',
            'Johnston Atoll',
            'Kingman Reef',
            'Kiribati',
            'Marshall Islands',
            'Micronesia, Federated States of',
            'Midway Islands',
            'Nauru',
            'New Caledonia',
            'New Zealand',
            'Niue',
            'Norfolk Island',
            'Northern Mariana Islands',
            'Palau',
            'Palmyra Atoll',
            'Pitcairn Islands',
            'Samoa',
            'Solomon Islands',
            'Tokelau',
            'Tonga',
            'Tuvalu',
            'United States Pacific Island Wildlife Refuges',
            'Vanuatu',
            'Wake Island',
            'Wallis and Futuna',
        ),
        'centralAmericaAndTheCaribbean' => array(
            'Anguilla',
            'Antigua and Barbuda',
            'Aruba',
            'Bahamas, The',
            'Barbados',
            'Belize',
            'British Virgin Islands',
            'Cayman Islands',
            'Costa Rica',
            'Cuba',
            'Dominica',
            'Dominican Republic',
            'El Salvador',
            'Grenada',
            'Guatemala',
            'Haiti',
            'Honduras',
            'Jamaica',
            'Montserrat',
            'Navassa Island',
            'Nicaragua',
            'Panama',
            'Puerto Rico',
            'Saint-Barthelemy',
            'Saint Kitts and Nevis',
            'Saint Lucia',
            'Saint Martin',
            'Saint Vincent and the Grenadines',
            'Trinidad and Tobago',
            'Turks and Caicos Islands',
            'United States Virgin Islands',
        ),
        'centralAsia' => array(
            'Kazakhstan',
            'Kyrgyzstan',
            'Russia',
            'Tajikistan',
            'Turkmenistan',
            'Uzbekistan',
        ),
        'eastAndSoutheastAsia' => array(
            'Brunei',
            'Burma',
            'Cambodia',
            'China',
            'Hong Kong',
            'Indonesia',
            'Japan',
            'Korea, North',
            'Korea, South',
            'Laos',
            'Macau',
            'Malaysia',
            'Mongolia',
            'Papua New Guinea',
            'Paracel Islands',
            'Philippines',
            'Singapore',
            'Spratly Islands',
            'Taiwan',
            'Thailand',
            'Timor-Leste',
            'Vietnam',
        ),
        'europe' => array(
            'Akrotiri',
            'Albania',
            'Andorra',
            'Austria',
            'Belarus',
            'Belgium',
            'Bosnia & Herzegovina',
            'Bulgaria',
            'Croatia',
            'Cyprus',
            'Czech Republic',
            'Denmark',
            'Dhekelia',
            'Estonia',
            'European Union',
            'Faroe Islands',
            'Finland',
            'France',
            'Germany',
            'Gibraltar',
            'Greece',
            'Guernsey',
            'Holy See (Vatican City)',
            'Hungary',
            'Iceland',
            'Ireland',
            'Isle of Man',
            'Italy',
            'Jan Mayen',
            'Jersey',
            'Kazakhstan',
            'Kosovo, Republic of',
            'Latvia',
            'Liechtenstein',
            'Lithuania',
            'Luxembourg',
            'Macedonia',
            'Malta',
            'Moldova',
            'Monaco',
            'Montenegro',
            'Netherlands',
            'Norway',
            'Poland',
            'Portugal',
            'Romania',
            'San Marino',
            'Serbia',
            'Slovakia',
            'Slovenia',
            'Spain',
            'Svalbard',
            'Sweden',
            'Switzerland',
            'Ukraine',
            'United Kingdom of Great Britain and Northern Ireland',
        ),
        'middleEast' => array(
            'Armenia',
            'Azerbaijan',
            'Bahrain',
            'Gaza Strip',
            'Georgia',
            'Iran',
            'Iraq',
            'Israel',
            'Jordan',
            'Kuwait',
            'Lebanon',
            'Oman',
            'Palestine',
            'Qatar',
            'Saudi Arabia',
            'Syria',
            'United Arab Emirates',
            'West Bank and Gaza',
            'Yemen',
        ),
        'northAmerica' => array(
            'Bermuda',
            'Canada ',
            'Clipperton Island',
            'Greenland',
            'Mexico',
            'Saint Pierre and Miquelon',
            'United States of America',
        ),
        'southAmerica' => array(
            'Argentina',
            'Bolivia',
            'Brazil',
            'Chile',
            'Colombia',
            'Ecuador',
            'Falkland Islands',
            'French Guiana',
            'Guyana',
            'Paraguay',
            'Peru',
            'South Georgia and South Sandwich Islands',
            'Suriname',
            'Uruguay',
            'Venezuela',
        ),
        'southAsia' => array(
            'Afghanistan',
            'Bangladesh',
            'Bhutan',
            'British Indian Ocean Territory',
            'India',
            'Maldives',
            'Nepal',
            'Pakistan',
            'Sri Lanka',
        ),
        
    );

    /**
     * Gets all the countries
     *
     * @return array of countries with the key and value being the same
     */
    public static function getAllCountries()
    {
        $countryList = array(); 
        
        foreach(self::$_countriesByRegionList as $region=>$list) { 
            foreach($list as $name) {
                $countryList[$name] = $name;
            }
        } 
        
        ksort($countryList);
        
        return $countryList;
    }
    
    /**
     * Gets all the regions
     *
     * @return array of regions
     */
    public static function getAllRegions()
    {
        return self::$_regionList;
    }
    
    /**
     * Gets the list of countries for the given region
     *
     * @return array of countries
     */
    public function getCountriesByRegion($region)
    {
        if(array_key_exists($region, self::$_countriesByRegionList)) {
            return self::$_countriesByRegionList[$region];
        } else {
            return false;
        }
    }
    
    /**
     * Gets the full region name for a given region key
     *
     * @return string : region name
     */
    public function getRegion($region)
    {
        if(array_key_exists($region, self::$_regionList)) {
            return self::$_regionList[$region];
        } else {
            return false;
        }
    }
    
    /**
     * Gets the region for the given country
     *
     * @return array with region key and region full name
     */
    public function getRegionByCountry($country)
    {
        $data = array();
        foreach(self::$_countriesByRegionList as $key=>$value) {
            if(in_array($country, $value)) {
                $data['region'] = $key;
                $data['regionName'] = self::$_regionList[$key];
                return $data;
            } 
        }
        return false;
        
    }
}