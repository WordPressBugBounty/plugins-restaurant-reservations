<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbSettings' ) ) {
/**
 * Class to handle configurable settings for Restaurant Reservations
 *
 * @since 0.0.1
 */
class rtbSettings {

	/**
	 * Default values for settings
	 * @since 0.0.1
	 */
	public $defaults = array();

	/**
	 * Stored values for settings
	 * @since 0.0.1
	 */
	public $settings = array();

	/**
	 * Should a premium setting be disabled or not
	 * @since 2.4.0
	 */
	public $premium_permissions = array();

	/**
	 * Columns which can be selected for the front-end view bookings form
	 */
	public $view_bookings_column_options = array();

	/**
	 * Currencies accepted for deposits
	 */
	public $currency_options = array(
	  'AUD' => 'Australian Dollar',
	  'BRL' => 'Brazilian Real',
	  'CAD' => 'Canadian Dollar',
	  'CZK' => 'Czech Koruna',
	  'DKK' => 'Danish Krone',
	  'EUR' => 'Euro',
	  'HKD' => 'Hong Kong Dollar',
	  'HUF' => 'Hungarian Forint',
	  'ILS' => 'Israeli New Sheqel',
	  'JPY' => 'Japanese Yen',
	  'MYR' => 'Malaysian Ringgit',
	  'MXN' => 'Mexican Peso',
	  'NOK' => 'Norwegian Krone',
	  'NZD' => 'New Zealand Dollar',
	  'PHP' => 'Philippine Peso',
	  'PLN' => 'Polish Zloty',
	  'GBP' => 'Pound Sterling',
	  'RUB' => 'Russian Ruble',
	  'SGD' => 'Singapore Dollar',
	  'SEK' => 'Swedish Krona',
	  'CHF' => 'Swiss Franc',
	  'TWD' => 'Taiwan New Dollar',
	  'THB' => 'Thai Baht',
	  'TRY' => 'Turkish Lira',
	  'USD' => 'U.S. Dollar'
	);

	/**
	 * Payment gateways that can be used for deposits
	 */
	public $payment_gateway_options = array();

	/**
	 * Languages supported by the pickadate library
	 */
	public $supported_i8n = array(
		'ar'	=> 'ar',
		'bg_BG'	=> 'bg_BG',
		'bs_BA'	=> 'bs_BA',
		'ca_ES'	=> 'ca_ES',
		'cs_CZ'	=> 'cs_CZ',
		'da_DK'	=> 'da_DK',
		'de_DE'	=> 'de_DE',
		'el_GR'	=> 'el_GR',
		'es_ES'	=> 'es_ES',
		'et_EE'	=> 'et_EE',
		'eu_ES'	=> 'eu_ES',
		'fa_IR'	=> 'fa_IR',
		'fi_FI'	=> 'fi_FI',
		'fr_FR'	=> 'fr_FR',
		'gl_ES'	=> 'gl_ES',
		'he_IL'	=> 'he_IL',
		'hi_IN'	=> 'hi_IN',
		'hr_HR'	=> 'hr_HR',
		'hu_HU'	=> 'hu_HU',
		'id_ID'	=> 'id_ID',
		'is_IS'	=> 'is_IS',
		'it_IT'	=> 'it_IT',
		'ja_JP'	=> 'ja_JP',
		'ko_KR'	=> 'ko_KR',
		'lt_LT'	=> 'lt_LT',
		'lv_LV'	=> 'lv_LV',
		'nb_NO'	=> 'nb_NO',
		'ne_NP'	=> 'ne_NP',
		'nl_NL'	=> 'nl_NL',
		'no_NO'	=> 'no_NO', // Old norwegian translation kept for backwards compatibility
		'pl_PL'	=> 'pl_PL',
		'pt_BR'	=> 'pt_BR',
		'pt_PT'	=> 'pt_PT',
		'ro_RO'	=> 'ro_RO',
		'ru_RU'	=> 'ru_RU',
		'sk_SK'	=> 'sk_SK',
		'sl_SI'	=> 'sl_SI',
		'sv_SE'	=> 'sv_SE',
		'th_TH'	=> 'th_TH',
		'tr_TR'	=> 'tr_TR',
		'uk_UA'	=> 'uk_UA',
		'zh_CN'	=> 'zh_CN',
		'zh_TW'	=> 'zh_TW',
	);

	public $country_phone_array = array(
		// 'AD' => array( 'name' => 'ANDORRA', 'code' => '376' ),
		// 'AE' => array( 'name' => 'UNITED ARAB EMIRATES', 'code' => '971' ),
		// 'AF' => array( 'name' => 'AFGHANISTAN', 'code' => '93' ),
		// 'AG' => array( 'name' => 'ANTIGUA AND BARBUDA', 'code' => '1268' ),
		// 'AI' => array( 'name' => 'ANGUILLA', 'code' => '1264' ),
		// 'AL' => array( 'name' => 'ALBANIA', 'code' => '355' ),
		// 'AM' => array( 'name' => 'ARMENIA', 'code' => '374' ),
		// 'AN' => array( 'name' => 'NETHERLANDS ANTILLES', 'code' => '599' ),
		// 'AO' => array( 'name' => 'ANGOLA', 'code' => '244' ),
		// 'AQ' => array( 'name' => 'ANTARCTICA', 'code' => '672' ),
		'AR' => array( 'name' => 'ARGENTINA', 'code' => '54' ),
		// 'AS' => array( 'name' => 'AMERICAN SAMOA', 'code' => '1684' ),
		'AT' => array( 'name' => 'AUSTRIA', 'code' => '43' ),
		'AU' => array( 'name' => 'AUSTRALIA', 'code' => '61' ),
		// 'AW' => array( 'name' => 'ARUBA', 'code' => '297' ),
		// 'AZ' => array( 'name' => 'AZERBAIJAN', 'code' => '994' ),
		// 'BA' => array( 'name' => 'BOSNIA AND HERZEGOVINA', 'code' => '387' ),
		// 'BB' => array( 'name' => 'BARBADOS', 'code' => '1246' ),
		// 'BD' => array( 'name' => 'BANGLADESH', 'code' => '880' ),
		'BE' => array( 'name' => 'BELGIUM', 'code' => '32' ),
		// 'BF' => array( 'name' => 'BURKINA FASO', 'code' => '226' ),
		'BG' => array( 'name' => 'BULGARIA', 'code' => '359' ),
		// 'BH' => array( 'name' => 'BAHRAIN', 'code' => '973' ),
		// 'BI' => array( 'name' => 'BURUNDI', 'code' => '257' ),
		// 'BJ' => array( 'name' => 'BENIN', 'code' => '229' ),
		// 'BL' => array( 'name' => 'SAINT BARTHELEMY', 'code' => '590' ),
		// 'BM' => array( 'name' => 'BERMUDA', 'code' => '1441' ),
		// 'BN' => array( 'name' => 'BRUNEI DARUSSALAM', 'code' => '673' ),
		// 'BO' => array( 'name' => 'BOLIVIA', 'code' => '591' ),
		'BR' => array( 'name' => 'BRAZIL', 'code' => '55' ),
		// 'BS' => array( 'name' => 'BAHAMAS', 'code' => '1242' ),
		// 'BT' => array( 'name' => 'BHUTAN', 'code' => '975' ),
		// 'BW' => array( 'name' => 'BOTSWANA', 'code' => '267' ),
		// 'BY' => array( 'name' => 'BELARUS', 'code' => '375' ),
		// 'BZ' => array( 'name' => 'BELIZE', 'code' => '501' ),
		'CA' => array( 'name' => 'CANADA', 'code' => '1' ),
		// 'CC' => array( 'name' => 'COCOS (KEELING) ISLANDS', 'code' => '61' ),
		// 'CD' => array( 'name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'code' => '243' ),
		// 'CF' => array( 'name' => 'CENTRAL AFRICAN REPUBLIC', 'code' => '236' ),
		// 'CG' => array( 'name' => 'CONGO', 'code' => '242' ),
		'CH' => array( 'name' => 'SWITZERLAND', 'code' => '41' ),
		// 'CI' => array( 'name' => 'COTE D IVOIRE', 'code' => '225' ),
		// 'CK' => array( 'name' => 'COOK ISLANDS', 'code' => '682' ),
		// 'CL' => array( 'name' => 'CHILE', 'code' => '56' ),
		// 'CM' => array( 'name' => 'CAMEROON', 'code' => '237' ),
		'CN' => array( 'name' => 'CHINA', 'code' => '86' ),
		// 'CO' => array( 'name' => 'COLOMBIA', 'code' => '57' ),
		// 'CR' => array( 'name' => 'COSTA RICA', 'code' => '506' ),
		// 'CU' => array( 'name' => 'CUBA', 'code' => '53' ),
		// 'CV' => array( 'name' => 'CAPE VERDE', 'code' => '238' ),
		// 'CX' => array( 'name' => 'CHRISTMAS ISLAND', 'code' => '61' ),
		// 'CY' => array( 'name' => 'CYPRUS', 'code' => '357' ),
		'CZ' => array( 'name' => 'CZECH REPUBLIC', 'code' => '420' ),
		'DE' => array( 'name' => 'GERMANY', 'code' => '49' ),
		// 'DJ' => array( 'name' => 'DJIBOUTI', 'code' => '253' ),
		'DK' => array( 'name' => 'DENMARK', 'code' => '45' ),
		// 'DM' => array( 'name' => 'DOMINICA', 'code' => '1767' ),
		// 'DO' => array( 'name' => 'DOMINICAN REPUBLIC', 'code' => '1809' ),
		// 'DZ' => array( 'name' => 'ALGERIA', 'code' => '213' ),
		// 'EC' => array( 'name' => 'ECUADOR', 'code' => '593' ),
		'EE' => array( 'name' => 'ESTONIA', 'code' => '372' ),
		// 'EG' => array( 'name' => 'EGYPT', 'code' => '20' ),
		// 'ER' => array( 'name' => 'ERITREA', 'code' => '291' ),
		'ES' => array( 'name' => 'SPAIN', 'code' => '34' ),
		// 'ET' => array( 'name' => 'ETHIOPIA', 'code' => '251' ),
		'FI' => array( 'name' => 'FINLAND', 'code' => '358' ),
		// 'FJ' => array( 'name' => 'FIJI', 'code' => '679' ),
		// 'FK' => array( 'name' => 'FALKLAND ISLANDS (MALVINAS)', 'code' => '500' ),
		// 'FM' => array( 'name' => 'MICRONESIA, FEDERATED STATES OF', 'code' => '691' ),
		// 'FO' => array( 'name' => 'FAROE ISLANDS', 'code' => '298' ),
		'FR' => array( 'name' => 'FRANCE', 'code' => '33' ),
		// 'GA' => array( 'name' => 'GABON', 'code' => '241' ),
		'GB' => array( 'name' => 'UNITED KINGDOM', 'code' => '44' ),
		// 'GD' => array( 'name' => 'GRENADA', 'code' => '1473' ),
		// 'GE' => array( 'name' => 'GEORGIA', 'code' => '995' ),
		// 'GH' => array( 'name' => 'GHANA', 'code' => '233' ),
		// 'GI' => array( 'name' => 'GIBRALTAR', 'code' => '350' ),
		'GL' => array( 'name' => 'GREENLAND', 'code' => '299' ),
		// 'GM' => array( 'name' => 'GAMBIA', 'code' => '220' ),
		// 'GN' => array( 'name' => 'GUINEA', 'code' => '224' ),
		// 'GQ' => array( 'name' => 'EQUATORIAL GUINEA', 'code' => '240' ),
		'GR' => array( 'name' => 'GREECE', 'code' => '30' ),
		// 'GT' => array( 'name' => 'GUATEMALA', 'code' => '502' ),
		// 'GU' => array( 'name' => 'GUAM', 'code' => '1671' ),
		// 'GW' => array( 'name' => 'GUINEA-BISSAU', 'code' => '245' ),
		// 'GY' => array( 'name' => 'GUYANA', 'code' => '592' ),
		'HK' => array( 'name' => 'HONG KONG', 'code' => '852' ),
		// 'HN' => array( 'name' => 'HONDURAS', 'code' => '504' ),
		'HR' => array( 'name' => 'CROATIA', 'code' => '385' ),
		// 'HT' => array( 'name' => 'HAITI', 'code' => '509' ),
		'HU' => array( 'name' => 'HUNGARY', 'code' => '36' ),
		'ID' => array( 'name' => 'INDONESIA', 'code' => '62' ),
		'IE' => array( 'name' => 'IRELAND', 'code' => '353' ),
		'IL' => array( 'name' => 'ISRAEL', 'code' => '972' ),
		// 'IM' => array( 'name' => 'ISLE OF MAN', 'code' => '44' ),
		'IN' => array( 'name' => 'INDIA', 'code' => '91' ),
		// 'IQ' => array( 'name' => 'IRAQ', 'code' => '964' ),
		// 'IR' => array( 'name' => 'IRAN, ISLAMIC REPUBLIC OF', 'code' => '98' ),
		'IS' => array( 'name' => 'ICELAND', 'code' => '354' ),
		'IT' => array( 'name' => 'ITALY', 'code' => '39' ),
		// 'JM' => array( 'name' => 'JAMAICA', 'code' => '1876' ),
		// 'JO' => array( 'name' => 'JORDAN', 'code' => '962' ),
		'JP' => array( 'name' => 'JAPAN', 'code' => '81' ),
		// 'KE' => array( 'name' => 'KENYA', 'code' => '254' ),
		// 'KG' => array( 'name' => 'KYRGYZSTAN', 'code' => '996' ),
		// 'KH' => array( 'name' => 'CAMBODIA', 'code' => '855' ),
		// 'KI' => array( 'name' => 'KIRIBATI', 'code' => '686' ),
		// 'KM' => array( 'name' => 'COMOROS', 'code' => '269' ),
		// 'KN' => array( 'name' => 'SAINT KITTS AND NEVIS', 'code' => '1869' ),
		// 'KP' => array( 'name' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF', 'code' => '850' ),
		'KR' => array( 'name' => 'KOREA REPUBLIC OF', 'code' => '82' ),
		// 'KW' => array( 'name' => 'KUWAIT', 'code' => '965' ),
		// 'KY' => array( 'name' => 'CAYMAN ISLANDS', 'code' => '1345' ),
		// 'KZ' => array( 'name' => 'KAZAKSTAN', 'code' => '7' ),
		// 'LA' => array( 'name' => 'LAO PEOPLES DEMOCRATIC REPUBLIC', 'code' => '856' ),
		// 'LB' => array( 'name' => 'LEBANON', 'code' => '961' ),
		// 'LC' => array( 'name' => 'SAINT LUCIA', 'code' => '1758' ),
		'LI' => array( 'name' => 'LIECHTENSTEIN', 'code' => '423' ),
		// 'LK' => array( 'name' => 'SRI LANKA', 'code' => '94' ),
		// 'LR' => array( 'name' => 'LIBERIA', 'code' => '231' ),
		// 'LS' => array( 'name' => 'LESOTHO', 'code' => '266' ),
		'LT' => array( 'name' => 'LITHUANIA', 'code' => '370' ),
		'LU' => array( 'name' => 'LUXEMBOURG', 'code' => '352' ),
		'LV' => array( 'name' => 'LATVIA', 'code' => '371' ),
		// 'LY' => array( 'name' => 'LIBYAN ARAB JAMAHIRIYA', 'code' => '218' ),
		// 'MA' => array( 'name' => 'MOROCCO', 'code' => '212' ),
		// 'MC' => array( 'name' => 'MONACO', 'code' => '377' ),
		// 'MD' => array( 'name' => 'MOLDOVA, REPUBLIC OF', 'code' => '373' ),
		'ME' => array( 'name' => 'MONTENEGRO', 'code' => '382' ),
		// 'MF' => array( 'name' => 'SAINT MARTIN', 'code' => '1599' ),
		// 'MG' => array( 'name' => 'MADAGASCAR', 'code' => '261' ),
		// 'MH' => array( 'name' => 'MARSHALL ISLANDS', 'code' => '692' ),
		// 'MK' => array( 'name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'code' => '389' ),
		// 'ML' => array( 'name' => 'MALI', 'code' => '223' ),
		// 'MM' => array( 'name' => 'MYANMAR', 'code' => '95' ),
		// 'MN' => array( 'name' => 'MONGOLIA', 'code' => '976' ),
		// 'MO' => array( 'name' => 'MACAU', 'code' => '853' ),
		// 'MP' => array( 'name' => 'NORTHERN MARIANA ISLANDS', 'code' => '1670' ),
		// 'MR' => array( 'name' => 'MAURITANIA', 'code' => '222' ),
		// 'MS' => array( 'name' => 'MONTSERRAT', 'code' => '1664' ),
		// 'MT' => array( 'name' => 'MALTA', 'code' => '356' ),
		// 'MU' => array( 'name' => 'MAURITIUS', 'code' => '230' ),
		// 'MV' => array( 'name' => 'MALDIVES', 'code' => '960' ),
		// 'MW' => array( 'name' => 'MALAWI', 'code' => '265' ),
		'MX' => array( 'name' => 'MEXICO', 'code' => '52' ),
		'MY' => array( 'name' => 'MALAYSIA', 'code' => '60' ),
		// 'MZ' => array( 'name' => 'MOZAMBIQUE', 'code' => '258' ),
		// 'NA' => array( 'name' => 'NAMIBIA', 'code' => '264' ),
		// 'NC' => array( 'name' => 'NEW CALEDONIA', 'code' => '687' ),
		// 'NE' => array( 'name' => 'NIGER', 'code' => '227' ),
		// 'NG' => array( 'name' => 'NIGERIA', 'code' => '234' ),
		// 'NI' => array( 'name' => 'NICARAGUA', 'code' => '505' ),
		'NL' => array( 'name' => 'NETHERLANDS', 'code' => '31' ),
		'NO' => array( 'name' => 'NORWAY', 'code' => '47' ),
		// 'NP' => array( 'name' => 'NEPAL', 'code' => '977' ),
		// 'NR' => array( 'name' => 'NAURU', 'code' => '674' ),
		// 'NU' => array( 'name' => 'NIUE', 'code' => '683' ),
		'NZ' => array( 'name' => 'NEW ZEALAND', 'code' => '64' ),
		// 'OM' => array( 'name' => 'OMAN', 'code' => '968' ),
		// 'PA' => array( 'name' => 'PANAMA', 'code' => '507' ),
		// 'PE' => array( 'name' => 'PERU', 'code' => '51' ),
		// 'PF' => array( 'name' => 'FRENCH POLYNESIA', 'code' => '689' ),
		// 'PG' => array( 'name' => 'PAPUA NEW GUINEA', 'code' => '675' ),
		// 'PH' => array( 'name' => 'PHILIPPINES', 'code' => '63' ),
		// 'PK' => array( 'name' => 'PAKISTAN', 'code' => '92' ),
		'PL' => array( 'name' => 'POLAND', 'code' => '48' ),
		// 'PM' => array( 'name' => 'SAINT PIERRE AND MIQUELON', 'code' => '508' ),
		// 'PN' => array( 'name' => 'PITCAIRN', 'code' => '870' ),
		'PR' => array( 'name' => 'PUERTO RICO', 'code' => '1' ),
		'PT' => array( 'name' => 'PORTUGAL', 'code' => '351' ),
		// 'PW' => array( 'name' => 'PALAU', 'code' => '680' ),
		// 'PY' => array( 'name' => 'PARAGUAY', 'code' => '595' ),
		// 'QA' => array( 'name' => 'QATAR', 'code' => '974' ),
		'RO' => array( 'name' => 'ROMANIA', 'code' => '40' ),
		'RS' => array( 'name' => 'SERBIA', 'code' => '381' ),
		'RU' => array( 'name' => 'RUSSIAN FEDERATION', 'code' => '7' ),
		// 'RW' => array( 'name' => 'RWANDA', 'code' => '250' ),
		// 'SA' => array( 'name' => 'SAUDI ARABIA', 'code' => '966' ),
		// 'SB' => array( 'name' => 'SOLOMON ISLANDS', 'code' => '677' ),
		// 'SC' => array( 'name' => 'SEYCHELLES', 'code' => '248' ),
		// 'SD' => array( 'name' => 'SUDAN', 'code' => '249' ),
		'SE' => array( 'name' => 'SWEDEN', 'code' => '46' ),
		'SG' => array( 'name' => 'SINGAPORE', 'code' => '65' ),
		// 'SH' => array( 'name' => 'SAINT HELENA', 'code' => '290' ),
		'SI' => array( 'name' => 'SLOVENIA', 'code' => '386' ),
		'SK' => array( 'name' => 'SLOVAKIA', 'code' => '421' ),
		// 'SL' => array( 'name' => 'SIERRA LEONE', 'code' => '232' ),
		// 'SM' => array( 'name' => 'SAN MARINO', 'code' => '378' ),
		// 'SN' => array( 'name' => 'SENEGAL', 'code' => '221' ),
		// 'SO' => array( 'name' => 'SOMALIA', 'code' => '252' ),
		// 'SR' => array( 'name' => 'SURINAME', 'code' => '597' ),
		// 'ST' => array( 'name' => 'SAO TOME AND PRINCIPE', 'code' => '239' ),
		// 'SV' => array( 'name' => 'EL SALVADOR', 'code' => '503' ),
		// 'SY' => array( 'name' => 'SYRIAN ARAB REPUBLIC', 'code' => '963' ),
		// 'SZ' => array( 'name' => 'SWAZILAND', 'code' => '268' ),
		// 'TC' => array( 'name' => 'TURKS AND CAICOS ISLANDS', 'code' => '1649' ),
		// 'TD' => array( 'name' => 'CHAD', 'code' => '235' ),
		// 'TG' => array( 'name' => 'TOGO', 'code' => '228' ),
		'TH' => array( 'name' => 'THAILAND', 'code' => '66' ),
		// 'TJ' => array( 'name' => 'TAJIKISTAN', 'code' => '992' ),
		// 'TK' => array( 'name' => 'TOKELAU', 'code' => '690' ),
		// 'TL' => array( 'name' => 'TIMOR-LESTE', 'code' => '670' ),
		// 'TM' => array( 'name' => 'TURKMENISTAN', 'code' => '993' ),
		// 'TN' => array( 'name' => 'TUNISIA', 'code' => '216' ),
		// 'TO' => array( 'name' => 'TONGA', 'code' => '676' ),
		// 'TR' => array( 'name' => 'TURKEY', 'code' => '90' ),
		// 'TT' => array( 'name' => 'TRINIDAD AND TOBAGO', 'code' => '1868' ),
		// 'TV' => array( 'name' => 'TUVALU', 'code' => '688' ),
		'TW' => array( 'name' => 'TAIWAN', 'code' => '886' ),
		// 'TZ' => array( 'name' => 'TANZANIA, UNITED REPUBLIC OF', 'code' => '255' ),
		'UA' => array( 'name' => 'UKRAINE', 'code' => '380' ),
		// 'UG' => array( 'name' => 'UGANDA', 'code' => '256' ),
		'US' => array( 'name' => 'UNITED STATES', 'code' => '1' ),
		'UY' => array( 'name' => 'URUGUAY', 'code' => '598' ),
		// 'UZ' => array( 'name' => 'UZBEKISTAN', 'code' => '998' ),
		// 'VA' => array( 'name' => 'HOLY SEE (VATICAN CITY STATE)', 'code' => '39' ),
		// 'VC' => array( 'name' => 'SAINT VINCENT AND THE GRENADINES', 'code' => '1784' ),
		// 'VE' => array( 'name' => 'VENEZUELA', 'code' => '58' ),
		// 'VG' => array( 'name' => 'VIRGIN ISLANDS, BRITISH', 'code' => '1284' ),
		// 'VI' => array( 'name' => 'VIRGIN ISLANDS, U.S.', 'code' => '1340' ),
		'VN' => array( 'name' => 'VIETNAM', 'code' => '84' ),
		// 'VU' => array( 'name' => 'VANUATU', 'code' => '678' ),
		// 'WF' => array( 'name' => 'WALLIS AND FUTUNA', 'code' => '681' ),
		// 'WS' => array( 'name' => 'SAMOA', 'code' => '685' ),
		// 'XK' => array( 'name' => 'KOSOVO', 'code' => '381' ),
		// 'YE' => array( 'name' => 'YEMEN', 'code' => '967' ),
		// 'YT' => array( 'name' => 'MAYOTTE', 'code' => '262' ),
		'ZA' => array( 'name' => 'SOUTH AFRICA', 'code' => '27' ),
		// 'ZM' => array( 'name' => 'ZAMBIA', 'code' => '260' ),
		// 'ZW' => array( 'name' => 'ZIMBABWE', 'code' => '263' )
	);
	
	// Holds location setting switch options, if any
	public $location_options = array();

	// Holds timeslot setting switch options, if any
	public $timeslot_options = array();
	
	// Holds all of the created tables, sorted by number
	public $sorted_tables = array();

	public function __construct() {

		add_action( 'init', array( $this, 'check_permissions' ), 10 );

		add_action( 'init', array( $this, 'set_defaults' ), 11 );

		add_action( 'init', array( $this, 'set_selectable_options' ), 11 );

		add_action( 'init', array( $this, 'set_location_and_timeslot_options' ), 1001 ); // After multiple locations taxonomy registered

		add_action( 'init', array( $this, 'load_settings_panel' ), 1002 ); // After multiple locations taxonomy registered

		add_filter( 'rtb_settings_page', array( $this, 'maybe_add_location_and_timeslot_settings' ), 11 ); // After additional settings added

		// Order schedule exceptions and remove past exceptions
		add_filter( 'sanitize_option_rtb-settings', array( $this, 'clean_schedule_exceptions' ), 100 );

	}

	/**
	 * Pre-check the permissions to enable/disable settings as per the 
	 * current license type
	 * @return void
	 */
	public function check_permissions() {
		global $rtb_controller;

		$this->premium_permissions['premium_view_bookings'] = array();
		if ( ! $rtb_controller->permissions->check_permission('premium_view_bookings') ) {
			$this->premium_permissions['premium_view_bookings'] = array(
				'disabled' 				=> true,
				'disabled_image' 	=> '#',
				'purchase_link' 	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions['mailchimp'] = array();
		if ( ! $rtb_controller->permissions->check_permission('mailchimp') ) {
			$this->premium_permissions['mailchimp'] = array(
				'disabled' 				=> true,
				'disabled_image' 	=> '#',
				'purchase_link' 	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions['premium_seat_restrictions'] = array();
		if ( ! $rtb_controller->permissions->check_permission('premium_seat_restrictions') ) {
			$this->premium_permissions['premium_seat_restrictions'] = array(
				'disabled' 				=> true,
				'disabled_image' 	=> '#',
				'purchase_link' 	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions['premium_table_restrictions'] = array();
		if ( ! $rtb_controller->permissions->check_permission('premium_table_restrictions') ) {
			$this->premium_permissions['premium_table_restrictions'] = array(
				'disabled' 				=> true,
				'disabled_image' 	=> '#',
				'purchase_link' 	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox',
				'ultimate_needed' => 'Yes'
			);
		}

		$this->premium_permissions['designer'] = array();
		if ( ! $rtb_controller->permissions->check_permission('designer') ) {
			$this->premium_permissions['designer'] = array(
				'disabled' 				=> true,
				'disabled_image' 	=> '#',
				'purchase_link' 	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions['reminders'] = array();
		if ( ! $rtb_controller->permissions->check_permission('reminders') ) {
			$this->premium_permissions['reminders'] = array(
				'disabled' 				=> true,
				'disabled_image' 	=> '#',
				'purchase_link' 	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox',
				'ultimate_needed' => 'Yes'
			);
		}

		$this->premium_permissions['payments'] = array();
		if ( ! $rtb_controller->permissions->check_permission('payments') ) {
			$this->premium_permissions['payments'] = array(
				'disabled'		=> true,
				'disabled_image'=> '#',
				'purchase_link'	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox',
				'ultimate_needed' => 'Yes'
			);
		}

		$this->premium_permissions['export'] = array();
		if ( ! $rtb_controller->permissions->check_permission('export') ) {
			$this->premium_permissions['export'] = array(
				'disabled'		=> true,
				'disabled_image'=> '#',
				'purchase_link'	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions['styling'] = array();
		if ( ! $rtb_controller->permissions->check_permission('styling') ) {
			$this->premium_permissions['styling'] = array(
				'disabled'		=> true,
				'disabled_image'=> '#',
				'purchase_link'	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions['labelling'] = array();
		if ( ! $rtb_controller->permissions->check_permission('labelling') ) {
			$this->premium_permissions['labelling'] = array(
				'disabled'		=> true,
				'disabled_image'=> '#',
				'purchase_link'	=> 'https://www.fivestarplugins.com/plugins/five-star-restaurant-reservations/?utm_source=rtb_lockbox'
			);
		}

		$this->premium_permissions = apply_filters( 'rtb_settings_check_permissions', $this->premium_permissions );
	}

	/**
	 * Load the plugin's default settings
	 * @since 0.0.1
	 */
	public function set_defaults() {

		global $rtb_controller;

		$cancel_button_label = __( 'View/Cancel a Reservation', 'restaurant-reservations' );

		if ( $rtb_controller->settings->get_setting( 'require-deposit' ) ) {
			
			$cancel_button_label = __( 'View/Cancel Reservation or Settle Deposit', 'restaurant-reservations' );
		}

		$this->defaults = array(

			'auto-confirm-max-party-size'	=> 1,
			'rtb-dining-block-length'		=> '120_minutes',
			'success-message'				=> __( 'Thanks, your booking request is waiting to be confirmed. Updates will be sent to the email address you provided.', 'restaurant-reservations' ),
			'confirmed-message'				=> __( 'Thanks, your booking request has been automatically confirmed. We look forward to seeing you soon!', 'restaurant-reservations' ),
			'date-format'					=> _x( 'mmmm d, yyyy', 'Default date format for display. Must match formatting rules at http://amsul.ca/pickadate.js/date/#formats', 'restaurant-reservations' ),
			'time-format'					=> _x( 'h:i A', 'Default time format for display. Must match formatting rules at http://amsul.ca/pickadate.js/time/#formats', 'restaurant-reservations' ),
			'time-interval'					=> __( '30', 'Default interval in minutes when selecting a time.', 'restaurant-reservations' ),

			'daily-summary-address-send-time'	=> '00:00',

			'tables-graphic-location'	    => 'right',

			// Payment defaults
			'rtb-deposit-applicable'		=> 'always',
			'rtb-paypal-email'              => get_option( 'admin_email' ),
      		'rtb-stripe-mode'               => 'test',
      		'rtb-currency'                  => 'USD',
      		'rtb-stripe-currency-symbol'    => '$',
      		'rtb-currency-symbol-location'  => 'before',
      		'rtb-payment-gateway'           => array(),

			// Export defaults
			'ebfrtb-paper-size' 			=> 'A4',
			'ebfrtb-pdf-lib' 				=> 'mpdf',
			'ebfrtb-csv-date-format' 		=> get_option( 'date_format' ),

			'rtb-view-bookings-columns'		=> array(
				'time',
				'party',
				'name',
				'email',
				'phone',
				'table',
				'status',
				'details',
			),

			'table-sections'				=> array(),

			// MailChimp defaults
			'mc-optprompt' 					=> __( 'Sign up for our mailing list.', 'restaurant-reservations' ),

			//Labels
			'label-book-table'				=> __( 'Book a table', 'restaurant-reservations' ),
			'label-location'				=> __( 'Location', 'restaurant-reservations' ),
			'label-date'					=> __( 'Date', 'restaurant-reservations' ),
			'label-date-today'				=> __( 'Today', 'restaurant-reservations' ),
			'label-date-clear'				=> __( 'Clear', 'restaurant-reservations' ),
			'label-date-close'				=> __( 'Close', 'restaurant-reservations' ),
			'label-time'					=> __( 'Time', 'restaurant-reservations' ),
			'label-time-clear'				=> __( 'Clear', 'restaurant-reservations' ),
			'label-no-times-available'		=> __( 'There are currently no times available for booking on your selected date.', 'restaurant-reservations' ),
			'label-party'					=> __( 'Party', 'restaurant-reservations' ),
			'label-table-s'					=> __( 'Table(s)', 'restaurant-reservations' ),
			'label-table-min'				=> __( 'min.', 'restaurant-reservations' ),
			'label-table-max'				=> __( 'max.', 'restaurant-reservations' ),
			'label-contact-details'			=> __( 'Contact Details', 'restaurant-reservations' ),
			'label-name'					=> __( 'Name', 'restaurant-reservations' ),
			'label-email'					=> __( 'Email', 'restaurant-reservations' ),
			'label-phone'					=> __( 'Phone', 'restaurant-reservations' ),
			'label-add-message'				=> __( 'Add a Message', 'restaurant-reservations' ),
			'label-message'					=> __( 'Message', 'restaurant-reservations' ),
			'label-request-booking'			=> __( 'Request Booking', 'restaurant-reservations' ),
			'label-table-layout'			=> __( 'Table Layout', 'restaurant-reservations' ),

			'label-enter-date-to-book'									=> __( 'Please enter the date you would like to book.', 'restaurant-reservations' ),
			'label-date-entered-not-valid'								=> __( 'The date you entered is not valid. Please select from one of the dates in the calendar.', 'restaurant-reservations' ),
			'label-enter-time-to-book'									=> __( 'Please enter the time you would like to book.', 'restaurant-reservations' ),
			'label-time-entered-not-valid'								=> __( 'The time you entered is not valid. Please select from one of the times provided.', 'restaurant-reservations' ),
			'label-bookings-cannot-be-made-more-than-days-in-advance'	=> __( 'Sorry, bookings can not be made more than %s days in advance.', 'restaurant-reservations' ),
			'label-bookings-cannot-be-made-in-past'						=> __( 'Sorry, bookings can not be made in the past.', 'restaurant-reservations' ),
			'label-bookings-cannot-be-made-same-day'					=> __( 'Sorry, bookings can not be made for the same day.', 'restaurant-reservations' ),
			'label-bookings-must-be-made-more-than-days-in-advance'		=> __( 'Sorry, bookings must be made more than %s days in advance.', 'restaurant-reservations' ),
			'label-bookings-must-be-made-more-than-hours-in-advance'	=> __( 'Sorry, bookings must be made more than %s hours in advance.', 'restaurant-reservations' ),
			'label-bookings-must-be-made-more-than-minutes-in-advance'	=> __( 'Sorry, bookings must be made more than %s minutes in advance.', 'restaurant-reservations' ),
			'label-no-bookings-accepted-then'							=> __( 'Sorry, no bookings are being accepted then.', 'restaurant-reservations' ),
			'label-no-bookings-accepted-on-that-date'					=> __( 'Sorry, no bookings are being accepted on that date.', 'restaurant-reservations' ),
			'label-no-bookings-accepted-at-that-time'					=> __( 'Sorry, no bookings are being accepted at that time.', 'restaurant-reservations' ),
			'label-enter-name-for-booking'								=> __( 'Please enter a name for this booking.', 'restaurant-reservations' ),
			'label-how-many-people-in-party'							=> __( 'Please let us know how many people will be in your party.', 'restaurant-reservations' ),
			'label-only-accept-bookings-for-parties-up-to'				=> __( 'We only accept bookings for parties of up to %d people.', 'restaurant-reservations' ),
			'label-only-accept-bookings-for-parties-more-than'			=> __( 'We only accept bookings for parties of more than %d people.', 'restaurant-reservations' ),
			'label-enter-email-address-to-confirm-booking'				=> __( 'Please enter an email address so we can confirm your booking.', 'restaurant-reservations' ),
			'label-enter-valid-email-address-to-confirm-booking'		=> __( 'Please enter a valid email address so we can confirm your booking.', 'restaurant-reservations' ),
			'label-provide-phone-number-to-confirm-booking'				=> __( 'Please provide a phone number so we can confirm your booking.', 'restaurant-reservations' ),
			'label-select-table-for-booking'							=> __( 'Please select a table for your booking.', 'restaurant-reservations' ),
			'label-select-valid-table-for-booking'						=> __( 'Please select a valid table for your booking.', 'restaurant-reservations' ),
			'label-no-table-available'									=> __( 'No table available at this time. Please change your selection.', 'restaurant-reservations' ),
			'label-fill-out-recaptcha'									=> __( 'Please fill out the reCAPTCHA box before submitting.', 'restaurant-reservations' ),
			'label-fill-out-recaptcha-again'							=> __( 'Please fill out the reCAPTCHA box again and re-submit.', 'restaurant-reservations' ),
			'label-if-encounter-multiple-recaptcha-errors'				=> __( ' If you encounter reCAPTCHA error multiple times, please contact us.', 'restaurant-reservations' ),
			'label-complete-this-field-to-request-booking'				=> __( 'Please complete this field to request a booking.', 'restaurant-reservations' ),
			'label-booking-has-been-rejected'							=> __( 'Your booking has been rejected. Please call us if you would like to make a booking.', 'restaurant-reservations' ),
			'label-maximum-reservations-reached'						=> __( 'The maximum number of reservations for that timeslot has been reached. Please select a different timeslot.', 'restaurant-reservations' ),
			'label-maximum-seats-reached'								=> __( 'With your party, the maximum number of seats for that timeslot would be exceeded. Please select a different timeslot or reduce your party size.', 'restaurant-reservations' ),
			'label-booking-info-exactly-matches'						=> __( 'Your booking and personal information exactly matches another booking. If this was not caused by refreshing the page, please call us to make a booking.', 'restaurant-reservations' ),
			'label-something-went-wrong'								=> __( 'Something went wrong. Please try again and, if the issue persists, please contact us.', 'restaurant-reservations' ),

			'label-payment-gateway'			=> __( 'Payment Gateway', 'restaurant-reservations' ),
			'label-proceed-to-deposit'		=> __( 'Proceed to Deposit', 'restaurant-reservations' ),
			'label-request-or-deposit'		=> __( 'Request Booking or Proceed to Deposit', 'restaurant-reservations' ),
			'label-pay-via-paypal'			=> __( 'Pay via PayPal', 'restaurant-reservations' ),
			'label-deposit-required'		=> __( 'Deposit Required: ', 'restaurant-reservations' ),
			'label-deposit-placing-hold'	=> __( 'We are only placing a hold for the above amount on your payment instrument. You will be charged later.', 'restaurant-reservations' ),
			'label-card-detail'				=> __( 'Payment Details', 'restaurant-reservations' ),
			'label-card-number'				=> __( 'Card Number', 'restaurant-reservations' ),
			'label-cvc'						=> __( 'CVC', 'restaurant-reservations' ),
			'label-expiration'				=> __( 'Expiration (MM/YYYY)', 'restaurant-reservations' ),
			'label-please-wait'				=> __( 'Please wait. Do not refresh until the button enables or the page reloads.', 'restaurant-reservations' ),
			'label-make-deposit'			=> __( 'Make Deposit', 'restaurant-reservations' ),

			'label-modify-reservation'			=> $cancel_button_label,
			'label-modify-make-reservation'		=> __( 'Make a Reservation', 'restaurant-reservations' ),
			'label-modify-using-form'			=> __( 'Use the form below to find your reservation', 'restaurant-reservations' ),
			'label-modify-form-email'			=> __( 'Email:', 'restaurant-reservations' ),
			'label-modify-find-reservations'	=> __( 'Find Reservations', 'restaurant-reservations' ),
			'label-modify-no-bookings-found'	=> __( 'No bookings were found for the email address you entered.', 'restaurant-reservations' ),
			'label-modify-cancel'				=> __( 'Cancel', 'restaurant-reservations' ),
			'label-modify-cancelled'			=> __( 'Cancelled', 'restaurant-reservations' ),
			'label-modify-deposit'				=> __( 'Deposit', 'restaurant-reservations' ),
			'label-modify-guest'				=> __( 'guest', 'restaurant-reservations' ),
			'label-modify-guests'				=> __( 'guests', 'restaurant-reservations' ),

			'label-view-arrived'			=> __( 'Arrived', 'restaurant-reservations' ),
			'label-view-time'				=> __( 'Time', 'restaurant-reservations' ),
			'label-view-party'				=> __( 'Party', 'restaurant-reservations' ),
			'label-view-name'				=> __( 'Name', 'restaurant-reservations' ),
			'label-view-email'				=> __( 'Email', 'restaurant-reservations' ),
			'label-view-phone'				=> __( 'Phone', 'restaurant-reservations' ),
			'label-view-table'				=> __( 'Table', 'restaurant-reservations' ),
			'label-view-status'				=> __( 'Status', 'restaurant-reservations' ),
			'label-view-details'			=> __( 'Details', 'restaurant-reservations' ),
			'label-view-set-status-arrived'	=> __( 'Set reservation status to \'Arrived\'?', 'restaurant-reservations' ),
			'label-view-arrived-yes'		=> __( 'Yes', 'restaurant-reservations' ),
			'label-view-arrived-no'			=> __( 'No', 'restaurant-reservations' ),

			'label-cancel-link-tag'			=> __( 'Cancel booking', 'restaurant-reservations' ),
			'label-bookings-link-tag'		=> __( 'View pending bookings', 'restaurant-reservations' ),
			'label-confirm-link-tag'		=> __( 'Confirm this booking', 'restaurant-reservations' ),
			'label-close-link-tag'			=> __( 'Reject this booking', 'restaurant-reservations' ),

			// Email address where admin notifications should be sent
			'admin-email-address'			=> get_option( 'admin_email' ),
			'ultimate-purchase-email'		=> get_option( 'admin_email' ),

			// Name and email address which should appear in the Reply-To section of notification emails
			'reply-to-name'					=> get_bloginfo( 'name' ),
			'reply-to-address'				=> get_option( 'admin_email' ),

			// Email template sent to an admin when a new booking request is made
			'subject-booking-admin'			=> _x( 'New Booking Request', 'Default email subject for admin notifications of new bookings', 'restaurant-reservations' ),
			'template-booking-admin'		=> _x( 'A new booking request has been made at {site_name}:

{user_name}
{party} people
{date}

{bookings_link}
{confirm_link}
{close_link}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to the admin when a new booking request is made. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when a new booking request is made
			'subject-booking-user'			=> sprintf( _x( 'Your booking at %s is pending', 'Default email subject sent to user when they request a booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-booking-user'			=> _x( 'Thanks {user_name},

Your booking request is <strong>waiting to be confirmed</strong>.

Give us a few moments to make sure that we\'ve got space for you. You will receive another email from us soon. If this request was made outside of our normal working hours, we may not be able to confirm it until we\'re open again.

<strong>Your request details:</strong>
{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to an admin when a new booking request is made
			'subject-booking-confirmed-admin'	=> _x( 'New Confirmed Booking Request', 'Default email subject for admin notifications when a new confirmed booking is made', 'restaurant-reservations' ),
			'template-booking-confirmed-admin'	=> _x( 'A new confirmed booking has been made at {site_name}:

{user_name}
{party} people
{date}

{bookings_link}
{confirm_link}
{close_link}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to the admin when a new confirmed booking is made. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to an admin when a new booking request is made
			'subject-booking-cancelled-admin'	=> _x( 'Booking Request Cancelled', 'Default email subject for admin notifications of cancelled bookings', 'restaurant-reservations' ),
			'template-booking-cancelled-admin'	=> _x( 'A booking request has been cancelled at {site_name}:

{user_name}
{party} people
{date}

{bookings_link}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to the admin when a booking request is cancelled. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when a new booking request is made
			'subject-booking-user'			=> sprintf( _x( 'Your booking at %s is pending', 'Default email subject sent to user when they request a booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-booking-user'			=> _x( 'Thanks {user_name},

Your booking request is <strong>waiting to be confirmed</strong>.

Give us a few moments to make sure that we\'ve got space for you. You will receive another email from us soon. If this request was made outside of our normal working hours, we may not be able to confirm it until we\'re open again.

<strong>Your request details:</strong>
{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when a booking request is confirmed
			'subject-confirmed-user'		=> sprintf( _x( 'Your booking at %s is confirmed', 'Default email subject sent to user when their booking is confirmed. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-confirmed-user'		=> _x( 'Hi {user_name},

Your booking request has been <strong>confirmed</strong>. We look forward to seeing you soon.

<strong>Your booking:</strong>
{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when a booking request is rejected
			'subject-rejected-user'			=> sprintf( _x( 'Your booking at %s was not accepted', 'Default email subject sent to user when their booking is rejected. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-rejected-user'		=> _x( 'Hi {user_name},

Sorry, we could not accomodate your booking request. We\'re full or not open at the time you requested:

{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when their booking request is rejected. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when they cancel their booking
			'subject-booking-cancelled-user'	=> sprintf( _x( 'Your reservation at %s was cancelled', 'Default email subject sent to user after they cancel their booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-booking-cancelled-user'	=> _x( 'Hi {user_name},

Your reservation with the following details has been cancelled:

{date}
{user_name}
{party} people

If you were not the one to cancel this booking, please contact us.

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they cancel their booking. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when they have an upcoming booking
			'subject-reminder-user'			=> sprintf( _x( 'Reminder: Your reservation at %s', 'Default email subject sent to user as a reminder about for their booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-reminder-user'		=> _x( 'Reminder: You have a reservation {date} for {party} at {site_name}',
				'Default email sent to users as a reminder about their booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when they're late for their booking
			'subject-late-user'			=> sprintf( _x( 'You\'re late for your booking at %s', 'Default email subject sent to user when they are late for their booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-late-user'		=> _x( 'You had a reservation {date} for {party} at {site_name}',
				'Default email sent to users when they are late for their booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user after their booking
			'subject-post-reservation-follow-up-user'		=> sprintf( _x( 'Thanks for dining at %s', 'Default email subject sent to user when they are late for their booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-post-reservation-follow-up-user'		=> _x( 'We just wanted to thank you for dining at {site_name} on {date}. Would you like to book another meal with us? You can do so on our {booking_page_link}.',
				'Default email sent to users after their reservation. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email sent to a user with a custom update notice from the admin
			'subject-admin-notice'			=> sprintf( _x( 'Update regarding your booking at %s', 'Default email subject sent to users when the admin sends a custom notice email from the bookings panel.', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),

			// Email address used in the FROM header of all emails
			'from-email-address' => get_option( 'admin_email' ),
		);

		$i8n = str_replace( '-', '_', get_bloginfo( 'language' ) );
		if ( array_key_exists( $i8n, $this->supported_i8n ) ) {
			$this->defaults['i8n'] = $i8n;
		}

		$this->defaults = apply_filters( 'rtb_settings_defaults', $this->defaults, $this );
	}

	/**
	 * Allows any filterable select options to be changed
	 * @since 2.3.6
	 */
	public function set_selectable_options() {
		global $rtb_controller;

		$this->currency_options = apply_filters( 'rtb_payments_currency_options', $this->currency_options );

		$this->payment_gateway_options = $rtb_controller->payment_manager->get_available_gateway_list();

		$view_bookings_column_options = array(
			'time' 		=> __( 'Time', 'restaurant-reservations' ),
			'party' 	=> __( 'Party', 'restaurant-reservations' ),
			'name'	 	=> __( 'Name', 'restaurant-reservations' ),
			'email' 	=> __( 'Email', 'restaurant-reservations' ),
			'phone' 	=> __( 'Phone', 'restaurant-reservations' ),
			'table' 	=> __( 'Table', 'restaurant-reservations' ),
			'status' 	=> __( 'Status', 'restaurant-reservations' ),
			'details' 	=> __( 'Details', 'restaurant-reservations' ),
		);

		$fields = rtb_get_custom_fields();

		foreach ( $fields as $field ) {

			if ( $field->type == 'fieldset' ) { continue; }

			$view_bookings_column_options[ $field->slug ] = $field->title;
		}

		$this->view_bookings_column_options = $view_bookings_column_options;
	}

	/**
	 * Get a setting's value or fallback to a default if one exists
	 * @since 0.0.1
	 */
	public function get_setting( $setting, $location = false, $timeslot = false ) {

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'rtb-settings' );
		}

		if ( ! empty( $timeslot ) ) {

			if ( !empty( $this->settings[ $timeslot . '-' . $setting ] ) and $this->settings[ $timeslot . '-' . $setting ] !== '[]' ) {
				return apply_filters( 'rtb-setting-' . $setting, $this->settings[ $timeslot . '-' . $setting ] );
			}
	
			if ( !empty( $this->defaults[ $timeslot . '-' . $setting ] ) and $this->defaults[ $timeslot . '-' . $setting ] !== '[]' ) {
				return apply_filters( 'rtb-setting-' . $setting, $this->defaults[ $timeslot . '-' . $setting ] );
			}
		}
		
		if ( ! empty( $location ) ) {

			if ( !empty( $this->settings[ $location . '-' . $setting ] ) and $this->settings[ $location . '-' . $setting ] !== '[]' ) {
				return apply_filters( 'rtb-setting-' . $setting, $this->settings[ $location . '-' . $setting ] );
			}
	
			if ( !empty( $this->defaults[ $location . '-' . $setting ] ) and $this->defaults[ $location . '-' . $setting ] !== '[]' ) {
				return apply_filters( 'rtb-setting-' . $setting, $this->defaults[ $location . '-' . $setting ] );
			}
		}

		if ( !empty( $this->settings[ $setting ] ) and $this->settings[ $setting ] !== '[]' ) {
			return apply_filters( 'rtb-setting-' . $setting, $this->settings[ $setting ] );
		}

		if ( !empty( $this->defaults[ $setting ] ) and $this->defaults[ $setting ] !== '[]' ) {
			return apply_filters( 'rtb-setting-' . $setting, $this->defaults[ $setting ] );
		}

		return apply_filters( 'rtb-setting-' . $setting, null );
	}

	public function is_location_setting_enabled( $setting, $location ) {

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'rtb-settings' );
		}
		
		return ! empty( $this->settings[ $location . '-' . $setting ] );
	}

	/**
	 * Set a setting to a particular value
	 * @since 2.1.0
	 */
	public function set_setting( $setting, $value ) {

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'rtb-settings' );
		}
		
		$this->settings[ $setting ] = $value;
	}

	/**
	 * Save all setting, to be used with set_setting
	 * @since 2.1.0
	 */
	public function save_settings() {
		global $wp_object_cache;

		$wp_object_cache->delete( 'alloptions', 'options' );
		
		update_option( 'rtb-settings', $this->settings );
	}

	/**
	 * Save all setting, to be used with set_setting
	 * @since 2.7.0
	 */
	public function set_location_and_timeslot_options() {
		global $rtb_controller;

		if ( $rtb_controller->locations->do_locations_exist() ) {
				
			$this->location_options = array(
				array( 
					'value' => '', 
					'label' => __( 'All', 'restaurant-reservations' )
				)
			);

			$args = array(
				'taxonomy'   => $rtb_controller->locations->location_taxonomy,
				'hide_empty' => false,
			);

			$terms = get_terms( $args );

			foreach ( $terms as $term ) {

				$this->location_options[] = array(
					'value'	=> $term->term_id,
					'label'	=> $term->name,
					'slug'	=> $term->slug,
					'type'	=> 'location',
				);
			}
		}

		if ( ! $rtb_controller->permissions->check_permission( 'premium_table_restrictions' ) ) { return; }

		$this->timeslot_options = array(
			array( 
				'value' => '', 
				'label' => __( 'All', 'restaurant-reservations' )
			)
		);

		if ( is_array( $this->get_setting( 'schedule-open' ) ) ) {

			foreach ( $this->get_setting( 'schedule-open' ) as $timeslot_id => $timeslot_values ) {

				$this->timeslot_options[] = array(
					'value'	=> 's_' . $timeslot_id,
					'label'	=> $this->get_timeslot_label( $timeslot_values ),
					'group'	=> 'Scheduling Rules',
					'slug'	=> 's_' . $timeslot_id,
					'type'	=> 'scheduling_rule',
				);
			}
		}

		if ( is_array( $this->get_setting( 'schedule-closed' ) ) ) {

			foreach ( $this->get_setting( 'schedule-closed' ) as $timeslot_id => $timeslot_values ) {

				$this->timeslot_options[] = array(
					'value'	=> 'e_' . $timeslot_id,
					'label'	=> $this->get_timeslot_label( $timeslot_values ),
					'group'	=> 'Exceptions',
					'slug'	=> 'e_' . $timeslot_id,
					'type'	=> 'scheduling_rule'
				);
			}
		}

		foreach ( $this->location_options as $location ) {

			if ( empty( $location['value'] ) ) { continue; } 

			if ( $this->is_location_setting_enabled( 'schedule-open', $location['slug'] ) and is_array( $this->get_setting( 'schedule-open', $location['slug'] ) ) ) { 

				foreach ( $this->get_setting( 'schedule-open', $location['slug'] ) as $timeslot_id => $timeslot_values ) {

					$this->timeslot_options[] = array(
						'value'	=> $location['slug'] . '_s_' . $timeslot_id,
						'label'	=> $location['label'] . ' - ' . $this->get_timeslot_label( $timeslot_values ),
						'group'	=> 'Scheduling Rules',
						'slug'	=> $location['slug'] . '_s_' . $timeslot_id,
						'type'	=> 'scheduling_rule',
					);
				}
			}

			if ( $this->is_location_setting_enabled( 'schedule-closed', $location['slug'] ) and is_array( $this->get_setting( 'schedule-closed', $location['slug'] ) ) ) { 

				foreach ( $this->get_setting( 'schedule-closed', $location['slug'] ) as $timeslot_id => $timeslot_values ) {

					$this->timeslot_options[] = array(
						'value'	=> $location['slug'] . '_e_' . $timeslot_id,
						'label'	=> $location['label'] . ' - ' . $this->get_timeslot_label( $timeslot_values ),
						'group'	=> 'Exceptions',
						'slug'	=> $location['slug'] . '_e_' . $timeslot_id,
						'type'	=> 'scheduling_rule'
					);
				}
			}
		}
	}

	/**
	 * Load the admin settings page
	 * @since 0.0.1
	 * @sa https://github.com/NateWr/simple-admin-pages
	 */
	public function load_settings_panel() {
		global $rtb_controller;

		require_once( RTB_PLUGIN_DIR . '/lib/simple-admin-pages/simple-admin-pages.php' );
		$sap = sap_initialize_library(
			$args = array(
				'version'       => '2.6.21.rtb',
				'theme'			=> 'blue',
				'lib_url'       => RTB_PLUGIN_URL . '/lib/simple-admin-pages/',
			)
		);

		$sap->add_page(
			'submenu',
			array(
				'id'            => 'rtb-settings',
				'title'         => __( 'Settings', 'restaurant-reservations' ),
				'menu_title'    => __( 'Settings', 'restaurant-reservations' ),
				'parent_menu'	=> 'rtb-bookings',
				'description'   => '',
				'capability'    => 'manage_options',
				'default_tab'   => 'rtb-schedule-tab',
			)
		);

		$settings_type_toggle_options = array();

		if ( ! empty( $this->location_options ) ) { $settings_type_toggle_options['location'] = $this->location_options; }

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            	=> 'rtb-schedule-tab',
				'title'         	=> __( 'Booking Schedule', 'restaurant-reservations' ),
				'is_tab'			=> true,
				'rank'				=> 1,
				'tutorial_yt_id'	=> 'fmMO_xn-9-8',
				'settings_type_toggle_options' => $settings_type_toggle_options
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-schedule',
				'title'         => __( 'Scheduling Options', 'restaurant-reservations' ),
				'tab'	          => 'rtb-schedule-tab',
			)
		);

		// Translateable strings for scheduler components
		$scheduler_strings = array(
			'add_rule'			=> __( 'Add new scheduling rule', 'restaurant-reservations' ),
			'weekly'			=> _x( 'Weekly', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'monthly'			=> _x( 'Monthly', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'date'				=> _x( 'Date', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'date_range' 	=> _x( 'Date Range', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'weekdays'			=> _x( 'Days of the week', 'Label for selecting days of the week in a scheduling rule', 'restaurant-reservations' ),
			'month_weeks'		=> _x( 'Weeks of the month', 'Label for selecting weeks of the month in a scheduling rule', 'restaurant-reservations' ),
			'date_label'		=> _x( 'Date', 'Label to select a date for a scheduling rule', 'restaurant-reservations' ),
			'time_label'		=> _x( 'Time', 'Label to select a time slot for a scheduling rule', 'restaurant-reservations' ),
			'allday'			=> _x( 'All day', 'Label to set a scheduling rule to last all day', 'restaurant-reservations' ),
			'start'				=> _x( 'Start', 'Label for the starting date/time of a scheduling rule', 'restaurant-reservations' ),
			'end'				=> _x( 'End', 'Label for the ending date/time of a scheduling rule', 'restaurant-reservations' ),
			'set_time_prompt'	=> _x( 'All day long. Want to %sset a time slot%s?', 'Prompt displayed when a scheduling rule is set without any time restrictions', 'restaurant-reservations' ),
			'toggle'			=> _x( 'Open and close this rule', 'Toggle a scheduling rule open and closed', 'restaurant-reservations' ),
			'delete'			=> _x( 'Delete rule', 'Delete a scheduling rule', 'restaurant-reservations' ),
			'delete_schedule'	=> __( 'Delete scheduling rule', 'restaurant-reservations' ),
			'never'				=> _x( 'Never', 'Brief default description of a scheduling rule when no weekdays or weeks are included in the rule', 'restaurant-reservations' ),
			'weekly_always'		=> _x( 'Every day', 'Brief default description of a scheduling rule when all the weekdays/weeks are included in the rule', 'restaurant-reservations' ),
			'monthly_weekdays'	=> _x( '%s on the %s week of the month', 'Brief default description of a scheduling rule when some weekdays are included on only some weeks of the month. %s should be left alone and will be replaced by a comma-separated list of days and weeks in the following format: M, T, W on the first, second week of the month', 'restaurant-reservations' ),
			'monthly_weeks'		=> _x( '%s week of the month', 'Brief default description of a scheduling rule when some weeks of the month are included but all or no weekdays are selected. %s should be left alone and will be replaced by a comma-separated list of weeks in the following format: First, second week of the month', 'restaurant-reservations' ),
			'all_day'			=> _x( 'All day', 'Brief default description of a scheduling rule when no times are set', 'restaurant-reservations' ),
			'before'			=> _x( 'Ends at', 'Brief default description of a scheduling rule when an end time is set but no start time. If the end time is 6pm, it will read: Ends at 6pm', 'restaurant-reservations' ),
			'after'				=> _x( 'Starts at', 'Brief default description of a scheduling rule when a start time is set but no end time. If the start time is 6pm, it will read: Starts at 6pm', 'restaurant-reservations' ),
			'separator'			=> _x( '&mdash;', 'Separator between times of a scheduling rule', 'restaurant-reservations' ),
			'date_range_from_today' => _x( 'From Today', 'When Date range start date is not set', 'restaurant-reservations' ),
			'date_range_upto_indefinite' => _x( 'Upto Indefinite', 'When Date range end date is not set', 'restaurant-reservations' ),
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'scheduler',
			array(
				'id'			=> 'schedule-open',
				'title'			=> __( 'Schedule', 'restaurant-reservations' ),
				'description'	=> __( 'Define the weekly schedule during which you accept bookings.', 'restaurant-reservations' ),
				'weekdays'		=> array(
					'monday'		=> _x( 'Mo', 'Monday abbreviation', 'restaurant-reservations' ),
					'tuesday'		=> _x( 'Tu', 'Tuesday abbreviation', 'restaurant-reservations' ),
					'wednesday'		=> _x( 'We', 'Wednesday abbreviation', 'restaurant-reservations' ),
					'thursday'		=> _x( 'Th', 'Thursday abbreviation', 'restaurant-reservations' ),
					'friday'		=> _x( 'Fr', 'Friday abbreviation', 'restaurant-reservations' ),
					'saturday'		=> _x( 'Sa', 'Saturday abbreviation', 'restaurant-reservations' ),
					'sunday'		=> _x( 'Su', 'Sunday abbreviation', 'restaurant-reservations' )
				),
				'time_format'	=> $this->get_setting( 'time-format' ),
				'date_format'	=> $this->get_setting( 'date-format' ),
				'disable_weeks'	=> true,
				'disable_date'	=> true,
				'disable_date_range'	=> true,
				'strings' => $scheduler_strings,
			)
		);

		$scheduler_strings['all_day'] = _x( 'Closed all day', 'Brief default description of a scheduling exception when no times are set', 'restaurant-reservations' );
		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'scheduler',
			array(
				'id'				=> 'schedule-closed',
				'title'				=> __( 'Exceptions', 'restaurant-reservations' ),
				'description'		=> __( "Define special opening hours for holidays, events or other needs. Leave the time empty if you're closed all day.", 'restaurant-reservations' ),
				'time_format'		=> esc_attr( $this->get_setting( 'time-format' ) ),
				'date_format'		=> esc_attr( $this->get_setting( 'date-format' ) ),
				'disable_weekdays'	=> true,
				'disable_weeks'		=> true,
				'strings' => $scheduler_strings,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'early-bookings',
				'title'         => __( 'Early Bookings', 'restaurant-reservations' ),
				'description'   => __( 'Select how early customers can make their booking. (Administrators and Booking Managers are not restricted by this setting.)', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => apply_filters( 'rtb_setting_early_booking_options', array(
						''		=> __( 'Any time', 'restaurant-reservations' ),
						'1' 	=> __( 'From 1 day in advance', 'restaurant-reservations' ),
						'7' 	=> __( 'From 1 week in advance', 'restaurant-reservations' ),
						'14' 	=> __( 'From 2 weeks in advance', 'restaurant-reservations' ),
						'30' 	=> __( 'From 30 days in advance', 'restaurant-reservations' ),
						'60' 	=> __( 'From 60 days in advance', 'restaurant-reservations' ),
						'90' 	=> __( 'From 90 days in advance', 'restaurant-reservations' ),
					)
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'late-bookings',
				'title'         => __( 'Late Bookings', 'restaurant-reservations' ),
				'description'   => __( 'Select how late customers can make their booking. (Administrators and Booking Managers are not restricted by this setting.)', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => apply_filters( 'rtb_setting_late_booking_options', array(
						'' 	       => __( 'Up to the last minute', 'restaurant-reservations' ),
						'15'       => __( 'At least 15 minutes in advance', 'restaurant-reservations' ),
						'30'       => __( 'At least 30 minutes in advance', 'restaurant-reservations' ),
						'45'       => __( 'At least 45 minutes in advance', 'restaurant-reservations' ),
						'60'       => __( 'At least 1 hour in advance', 'restaurant-reservations' ),
						'240'      => __( 'At least 4 hours in advance', 'restaurant-reservations' ),
						'1440'     => __( 'At least 24 hours in advance', 'restaurant-reservations' ),
						'same_day' => __( 'Block same-day bookings', 'restaurant-reservations' ),
					)
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'late-cancellations',
				'title'         => __( 'Late Cancellations', 'restaurant-reservations' ),
				'description'   => __( 'Select how late customers can cancel their booking. (Administrators and Booking Managers are not restricted by this setting.)', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => apply_filters( 'rtb_setting_late_cancellations_options', array(
						'' 	       => __( 'Up to the last minute', 'restaurant-reservations' ),
						'30'       => __( 'At least 30 minutes in advance', 'restaurant-reservations' ),
						'60'       => __( 'At least 1 hour in advance', 'restaurant-reservations' ),
						'240'      => __( 'At least 4 hours in advance', 'restaurant-reservations' ),
						'1440'     => __( 'At least 24 hours in advance', 'restaurant-reservations' ),
					)
				),
				'conditional_on'        => 'allow-cancellations',
				'conditional_on_value'  => true
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'			=> 'date-onload',
				'title'			=> __( 'Date Pre-Selection', 'restaurant-reservations' ),
				'description'	=> __( 'When the booking form is loaded, should it automatically attempt to select a valid date?', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'' 			=> __( 'Select today if valid', 'restaurant-reservations' ),
					'soonest'	=> __( 'Select today or next valid date', 'restaurant-reservations' ),
					'empty' 	=> __( 'Leave empty', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'			=> 'time-interval',
				'title'			=> __( 'Time Interval', 'restaurant-reservations' ),
				'description'	=> __( 'Select the number of minutes between each available time.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => apply_filters( 'rtb_setting_time_interval_options', array(
						'180' 		=> __( 'Every 180 minutes', 'restaurant-reservations' ),
						'120' 		=> __( 'Every 120 minutes', 'restaurant-reservations' ),
						'90' 		=> __( 'Every 90 minutes', 'restaurant-reservations' ),
						'60' 		=> __( 'Every 60 minutes', 'restaurant-reservations' ),
						'30' 		=> __( 'Every 30 minutes', 'restaurant-reservations' ),
						'15' 		=> __( 'Every 15 minutes', 'restaurant-reservations' ),
						'10' 		=> __( 'Every 10 minutes', 'restaurant-reservations' ),
						'5' 		=> __( 'Every 5 minutes', 'restaurant-reservations' ),
					)
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'week-start',
				'title'         => __( 'Week Starts On', 'restaurant-reservations' ),
				'description'	=> __( 'Select the first day of the week', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'0' => __( 'Sunday', 'restaurant-reservations' ),
					'1' => __( 'Monday', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            	=> 'rtb-basic',
				'title'         	=> __( 'Basic', 'restaurant-reservations' ),
				'is_tab'			=> true,
				'rank'				=> 2,
				'tutorial_yt_id'	=> '-RC2kUhXkLQ',
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-general',
				'title'         => __( 'General', 'restaurant-reservations' ),
				'tab'	          => 'rtb-basic',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'post',
			array(
				'id'            => 'booking-page',
				'title'         => __( 'Booking Page', 'restaurant-reservations' ),
				'description'   => __( 'Select a page on your site to automatically display the booking form and confirmation message.', 'restaurant-reservations' ),
				'blank_option'	=> true,
				'args'			=> array(
					'post_type' 		=> 'page',
					'posts_per_page'	=> -1,
					'post_status'		=> 'publish',
					'orderby'			=> 'title',
					'order'				=> 'ASC',
				),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'select',
			array(
				'id'            => 'party-size-min',
				'title'         => __( 'Min Party Size', 'restaurant-reservations' ),
				'description'   => __( 'Set a minimum allowed party size for bookings.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => $this->get_party_size_setting_options( false ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'select',
			array(
				'id'            => 'party-size',
				'title'         => __( 'Max Party Size', 'restaurant-reservations' ),
				'description'   => __( 'Set a maximum allowed party size for bookings.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => $this->get_party_size_setting_options(),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'toggle',
			array(
				'id'            => 'party-blank',
				'title'         => __( 'Blank Party Option', 'restaurant-reservations' ),
				'description'   => __( 'Include a blank option in the party size dropdown, so that users are forced to make a selection.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'select',
			array(
				'id'            => 'auto-confirm-max-party-size',
				'title'         => __( 'Automatically Confirm Below Party Size', 'restaurant-reservations' ),
				'description'   => __( 'Set a maximum party size below which all bookings will be automatically confirmed.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'default' 		=> $this->defaults['auto-confirm-max-party-size'],
				'options'       => $this->get_party_size_setting_options( false ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'toggle',
			array(
				'id'			=> 'allow-cancellations',
				'title'			=> __( 'Let Guests View and Cancel Bookings', 'restaurant-reservations' ),
				'description'	=> __( 'Adds an option to your booking form that lets guests view and/or cancel their upcoming bookings. If you have deposits enabled, then guests can also use this feature to make a payment for a deposit that wasn\'t paid at the time of the initial booking.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'toggle',
			array(
				'id'			=> 'show-cancelled-status',
				'title'			=> __( 'Show Cancelled Bookings in Admin', 'restaurant-reservations' ),
				'description'	=> __( 'By default, cancelled bookings will only show on the admin bookings screen if you have the above "Let Guests View and Cancel Bookings" option enabled. Enabling this option lets you display cancelled bookings even if the above option is disabled. (An example use case for this would be if you have added a cancel link to your customer emails.)', 'restaurant-reservations' ),
				'conditional_on'        => 'allow-cancellations',
				'conditional_on_value'  => false
				)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'select',
			array(
				'id'            => 'require-phone',
				'title'         => __( 'Require Phone', 'restaurant-reservations' ),
				'description'   => __( "Don't accept booking requests without a phone number.", 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'' => __( 'No', 'restaurant-reservations' ),
					'1' => __( 'Yes', 'restaurant-reservations' ),
				),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'textarea',
			array(
				'id'			=> 'success-message',
				'title'			=> __( 'Pending Confirmation Message', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the message to display when a booking request is made and is set to pending confirmation.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['success-message'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'textarea',
			array(
				'id'			=> 'confirmed-message',
				'title'			=> __( 'Confirmed Booking Message', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the message to display when a booking is made that has been automatically confirmed.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['confirmed-message'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'pending-redirect-page',
				'title'         => __( 'Pending Redirect Page', 'restaurant-reservations' ),
				'description'	=> __( 'Input the URL of the page you want the booking form to redirect to after a reservation is made that is set to pending. This overrides the "Pending Confirmation Message" text/option.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'confirmed-redirect-page',
				'title'         => __( 'Confirmed Redirect Page', 'restaurant-reservations' ),
				'description'	=> __( 'Input the URL of the page you want the booking form to redirect to after a reservation is made that is automatically confirmed. This overrides the "Confirmed Booking Message" text/option.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'cancelled-redirect-page',
				'title'         => __( 'Cancellation Redirect Page', 'restaurant-reservations' ),
				'description'	=> __( 'Input the URL of the page you want the cancellation form to redirect to when someone cancels their reservation. Only applicable if the "Let Guests View and Cancel Bookings" option above is enabled. If left blank, it will display a success message instead of redirecting.', 'restaurant-reservations' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-booking-form',
				'title'         => __( 'Booking Form', 'restaurant-reservations' ),
				'tab'	          => 'rtb-basic',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-booking-form',
			'text',
			array(
				'id'            => 'date-format',
				'title'         => __( 'Date Format', 'restaurant-reservations' ),
				'description'   => sprintf( __( 'Define how the date is formatted on the booking form. %sFormatting rules%s. This only changes the format on the booking form. To change the date format in notification messages, modify your general %sWordPress Settings%s.', 'restaurant-reservations' ), '<a href="http://amsul.ca/pickadate.js/date/#formats">', '</a>', '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' ),
				'placeholder'	=> $this->defaults['date-format'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-booking-form',
			'text',
			array(
				'id'            => 'time-format',
				'title'         => __( 'Time Format', 'restaurant-reservations' ),
				'description'   => sprintf( __( 'Define how the time is formatted on the booking form. %sFormatting rules%s. This only changes the format on the booking form. To change the time format in notification messages, modify your general %sWordPress Settings%s.', 'restaurant-reservations' ), '<a href="http://amsul.ca/pickadate.js/time/#formats">', '</a>', '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' ),
				'placeholder'	=> $this->defaults['time-format'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-booking-form',
			'toggle',
			array(
				'id'          => 'display-unavailable-time-slots',
				'title'       => __( 'Display Unavailable Time Slots', 'restaurant-reservations' ),
				'description' => __( 'Display any time slots (greyed out) which are not available for booking on the front end.' )
			)
		);

		// Add i8n setting for pickadate if the frontend assets are to be loaded
		if ( apply_filters( 'rtb-load-frontend-assets', true ) ) {
			$sap->add_setting(
				'rtb-settings',
				'rtb-general',
				'select',
				array(
					'id'            => 'i8n',
					'title'         => __( 'Language', 'restaurant-reservations' ),
					'description'   => __( 'Select a language to use for the booking form datepicker if it is different than your WordPress language setting.', 'restaurant-reservations' ),
					'options'		=> $this->supported_i8n,
				)
			);
		}

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'number',
			array(
				'id' 			=> 'refresh-booking-listing',
				'title' 		=> __( 'Refresh Bookings Page', 'restaurant-reservations' ),
				'description' 	=> __( 'After how many minutes should the Bookings page be automatically refreshed? The minimum is 1 minute and you can disable it by inputting 0 or leaving it empty.' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-security',
				'title'         => __( 'Security', 'restaurant-reservations' ),
				'tab'	       	=> 'rtb-basic',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-security',
			'textarea',
			array(
				'id'			=> 'ban-emails',
				'title'			=> __( 'Banned Email Addresses', 'restaurant-reservations' ),
				'description'	=> __( 'You can block bookings from specific email addresses. Enter each email address on a separate line.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-security',
			'textarea',
			array(
				'id'			=> 'ban-ips',
				'title'			=> __( 'Banned IP Addresses', 'restaurant-reservations' ),
				'description'	=> __( 'You can block bookings from specific IP addresses. Enter each IP address on a separate line. Be aware that many internet providers rotate their IP address assignments, so an IP address may accidentally refer to a different user. Also, if you block an IP address used by a public connection, such as cafe WIFI, a public library, or a university network, you may inadvertantly block several people.', 'restaurant-reservations' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-captcha',
				'title'         => __( 'Captcha', 'restaurant-reservations' ),
				'tab'	          => 'rtb-basic',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-captcha',
			'toggle',
			array(
				'id'			=> 'enable-captcha',
				'title'			=> __( 'Enable Google reCAPTCHA v2', 'restaurant-reservations' ),
				'description'			=> sprintf( __( 'Adds Google\'s reCAPTCHA code to your form, to verify guests before they can book. Please check %s our documentation %s for more information on how to configure this feature.', 'restaurant-reservations' ), '<a href="http://doc.fivestarplugins.com/plugins/restaurant-reservations/" target="_blank">', '</a>')
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-captcha',
			'text',
			array(
				'id'            => 'captcha-site-key',
				'title'         => __( 'Google Site Key', 'restaurant-reservations' ),
				'description'   => __( 'The site key provided to you by Google', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-captcha',
			'text',
			array(
				'id'            => 'captcha-secret-key',
				'title'         => __( 'Google Secret Key', 'restaurant-reservations' ),
				'description'   => __( 'The secret key provided to you by Google', 'restaurant-reservations' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-privacy',
				'title'         => __( 'Privacy', 'restaurant-reservations' ),
				'tab'			=> 'rtb-basic',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-privacy',
			'toggle',
			array(
				'id'			=> 'disable-ip-capture',
				'title'			=> __( 'Disable IP Capture', 'restaurant-reservations' ),
				'description'	=> __( 'This turns off the feature that captures the IP address of the device making the booking.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-privacy',
			'toggle',
			array(
				'id'			=> 'require-consent',
				'title'			=> __( 'Require Consent', 'restaurant-reservations' ),
				'description'	=> __( 'Require customers to consent to the collection of their details when making a booking. This may be required to comply with privacy laws in your country.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-privacy',
			'textarea',
			array(
				'id'			=> 'consent-statement',
				'title'			=> __( 'Consent Statement', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the statement you would like customers to confirm when making a booking.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-privacy',
			'post',
			array(
				'id'            => 'privacy-page',
				'title'         => __( 'Privacy Statement Page', 'restaurant-reservations' ),
				'description'   => __( 'Select a page on your site which contains a privacy statement. If selected, it will be linked to in your consent statement.', 'restaurant-reservations' ),
				'blank_option'	=> true,
				'args'			=> array(
					'post_type' 		=> 'page',
					'posts_per_page'	=> -1,
					'post_status'		=> 'publish',
					'orderby'			=> 'title',
					'order'				=> 'ASC',
				),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-privacy',
			'text',
			array(
				'id'            => 'delete-data-days',
				'title'         => __( 'Delete Reservation Data Days', 'restaurant-reservations' ),
				'description'   => __( 'Sets the approximate number of days booking data should be stored for. Leave blank to keep booking data indefinitely.', 'restaurant-reservations' ),
			)
		);
		

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            	=> 'rtb-notifications-tab',
				'title'         	=> __( 'Notifications', 'restaurant-reservations' ),
				'is_tab'			=> true,
				'rank'				=> 4,
				'tutorial_yt_id'	=> 's1LnEb6xuXw',
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications-daily-summary',
				'title'         => __( 'Daily Summary', 'restaurant-reservations' ),
				'tab'	          => 'rtb-notifications-tab',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-daily-summary',
			'text',
			array(
				'id'			=> 'daily-summary-address',
				'title'			=> __( 'Summary Email Address', 'restaurant-reservations' ),
				'description'	=> __( 'The email address, if any, where a daily summary of upcoming reservations should be emailed.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-daily-summary',
			'time',
			array(
				'id'			=> 'daily-summary-address-send-time',
				'title'			=> __( 'Summary Email Send Time', 'restaurant-reservations' ),
				'description'	=> __( 'What time should the summary email be sent at? This is based on your WordPress timezone setting.', 'restaurant-reservations' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications',
				'title'         => __( 'General', 'restaurant-reservations' ),
				'tab'	          => 'rtb-notifications-tab',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'reply-to-name',
				'title'			=> __( 'Reply-To Name', 'restaurant-reservations' ),
				'description'	=> __( 'The name which should appear in the Reply-To field of a user notification email', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['reply-to-name'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'reply-to-address',
				'title'			=> __( 'Reply-To Email Address', 'restaurant-reservations' ),
				'description'	=> __( 'The email address which should appear in the Reply-To field of a user notification email.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['reply-to-address'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'toggle',
			array(
				'id'			=> 'admin-email-option',
				'title'			=> __( 'Admin Notification', 'restaurant-reservations' ),
				'description'			=> __( 'Send an email notification to an administrator when a new booking is requested.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'toggle',
			array(
				'id'			=> 'admin-confirmed-email-option',
				'title'			=> __( 'Admin New Confirmed Notification', 'restaurant-reservations' ),
				'description'			=> __( 'Send an email notification to an administrator when a new confirmed booking is made.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'toggle',
			array(
				'id'			=> 'admin-cancelled-email-option',
				'title'			=> __( 'Admin Cancellation Notification', 'restaurant-reservations' ),
				'description'			=> __( 'Send an email notification to an administrator when a booking is cancelled.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'admin-email-address',
				'title'			=> __( 'Admin Email Address', 'restaurant-reservations' ),
				'description'	=> __( 'The email address where admin notifications should be sent.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['admin-email-address'],
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications-templates',
				'title'         => __( 'Notification Emails', 'restaurant-reservations' ),
				'tab'			=> 'rtb-notifications-tab',
				'description'	=> __( 'Adjust the messages that are emailed to users and admins during the booking process.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'html',
			array(
				'id'			=> 'template-tags-description',
				'title'			=> __( 'Template Tags', 'restaurant-reservations' ),
				'html'			=> '
					<p class="description">' . __( 'Use the following tags to automatically add booking information to the emails. Tags labeled with an asterisk (*) can be used in the email subject as well. Use the URL tags and not the links when sending SMS messages for best results.', 'restaurant-reservations' ) . '</p>' .
					$this->render_template_tag_descriptions(),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-admin',
				'title'			=> __( 'Admin Notification Subject (Pending Booking)', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject for admin notifications.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-admin',
				'title'			=> __( 'Admin Notification Email (Pending Booking)', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email an admin should receive when an initial booking request is made.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-confirmed-admin',
				'title'			=> __( 'Admin Notification Subject (Auto Confirmed Booking)', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject for admin notifications for automatically-confirmed bookings.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-confirmed-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-confirmed-admin',
				'title'			=> __( 'Admin Notification Email (Auto Confirmed Booking)', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email an admin should receive when an automatically-confirmed booking is made.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-confirmed-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-cancelled-admin',
				'title'			=> __( 'Admin Booking Cancelled Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject for admin notifications when a booking is cancelled.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-cancelled-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-cancelled-admin',
				'title'			=> __( 'Admin Booking Cancelled Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email an admin should receive when a booking is cancelled.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-cancelled-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-user',
				'title'			=> __( 'New Request Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when they make an initial booking request.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-user',
				'title'			=> __( 'New Request Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when they make an initial booking request.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-confirmed-user',
				'title'			=> __( 'Confirmed Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when their booking has been confirmed.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-confirmed-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-confirmed-user',
				'title'			=> __( 'Confirmed Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been confirmed.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-confirmed-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-rejected-user',
				'title'			=> __( 'Rejected Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when their booking has been rejected.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-rejected-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-rejected-user',
				'title'			=> __( 'Rejected Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been rejected.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-rejected-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-cancelled-user',
				'title'			=> __( 'Booking Cancelled Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when they have cancelled their booking.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-cancelled-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-cancelled-user',
				'title'			=> __( 'Booking Cancelled Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when they cancel their booking.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-cancelled-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-admin-notice',
				'title'			=> __( 'Admin Update Subject', 'restaurant-reservations' ),
				'description'	=> sprintf( __( 'The email subject a user should receive when an admin sends them a custom email message from the %sbookings panel%s.', 'restaurant-reservations' ), '<a href="' . admin_url( '?page=rtb-bookings' ) . '">', '</a>' ),
				'placeholder'	=> $this->defaults['subject-admin-notice'],
			)
		);

		$settings_type_toggle_options = array();

		if ( ! empty( $this->location_options ) ) { $settings_type_toggle_options['location'] = $this->location_options; }
		if ( ! empty( $this->timeslot_options ) ) { $settings_type_toggle_options['scheduling_rule'] = $this->timeslot_options; }

		/**
	     * Premium options preview only
	     */
	    // "Advanced" Tab
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'     				=> 'rtb-advanced-tab',
	        'title'  				=> __( 'Advanced', 'restaurant-reservations' ),
	        'is_tab' 				=> true,
	        'rank'	 				=> 3,
	        'tutorial_yt_id'		=> 'Mp6n8Ph0Pm4',
	        'settings_type_toggle_options' => $settings_type_toggle_options,
	        'show_submit_button' 	=> $this->show_submit_button( 'premium' )
	      )
	    );
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'       => 'rtb-advanced-tab-body',
	        'tab'      => 'rtb-advanced-tab',
	        'callback' => $this->premium_info( 'advanced' )
	      )
	    );
	
	    // "Payments" Tab
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'     				=> 'rtb-payments-tab',
	        'title'  				=> __( 'Payments', 'restaurant-reservations' ),
	        'is_tab' 				=> true,
	        'rank'	 				=> 5,
	        'tutorial_yt_id'		=> 'vEhvAOAWBk4',
	        'show_submit_button' 	=> $this->show_submit_button( 'payments' )
	      )
	    );
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'       => 'rtb-payments-tab-body',
	        'tab'      => 'rtb-payments-tab',
	        'callback' => $this->premium_info( 'payments' )
	      )
	    );
	
	    // "Export" Tab
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'     				=> 'rtb-export-tab',
	        'title'  				=> __( 'Export', 'restaurant-reservations' ),
	        'is_tab' 				=> true,
	        'rank'	 				=> 8,
	        'tutorial_yt_id'		=> '-FOrQSVVDj4',
	        'show_submit_button' 	=> $this->show_submit_button( 'export' )
	      )
	    );
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'       => 'rtb-export-tab-body',
	        'tab'      => 'rtb-export-tab',
	        'callback' => $this->premium_info( 'export' )
	      )
	    );

	    // "Labelling" Tab
		$sap->add_section(
		  'rtb-settings',
		  array(
		    'id'     				=> 'rtb-labelling-tab',
		    'title'  				=> __( 'Labelling', 'restaurant-reservations' ),
		    'is_tab' 				=> true,
		    'rank'	 				=> 7,
	        'tutorial_yt_id'		=> '1JG7PVu09nA',
		    'show_submit_button' 	=> $this->show_submit_button( 'labelling' )
		  )
		);
		$sap->add_section(
		  'rtb-settings',
		  array(
		    'id'       => 'rtb-labelling-tab-body',
		    'tab'      => 'rtb-labelling-tab',
		    'callback' => $this->premium_info( 'labelling' )
		  )
		);
	
	    // "Styling" Tab
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'     				=> 'rtb-styling-tab',
	        'title'  				=> __( 'Styling', 'restaurant-reservations' ),
	        'is_tab' 				=> true,
	        'rank'	 				=> 6,
	        'tutorial_yt_id'		=> 'JEuRu71ccPg',
	        'show_submit_button' 	=> $this->show_submit_button( 'styling' )
	      )
	    );
	    $sap->add_section(
	      'rtb-settings',
	      array(
	        'id'       => 'rtb-styling-tab-body',
	        'tab'      => 'rtb-styling-tab',
	        'callback' => $this->premium_info( 'styling' )
	      )
	    );

		$sap = apply_filters( 'rtb_settings_page', $sap, $this );

		$sap->add_admin_menus();

	}

	public function show_submit_button( $permission_type = '' ) {
		global $rtb_controller;
	
		if ( $rtb_controller->permissions->check_permission( $permission_type ) ) {
			return true;
		}

		return false;
	}
	
	public function premium_info( $section_and_perm_type ) {
		global $rtb_controller;

		$is_premium_user = $rtb_controller->permissions->check_permission( $section_and_perm_type );
		$is_helper_installed = defined( 'FSPPH_PLUGIN_FNAME' ) && is_plugin_active( FSPPH_PLUGIN_FNAME );

		if ( $is_premium_user || $is_helper_installed ) {
			return false;
		}

		$content = '';

		$premium_features = '
			<p><strong>' . __( 'The premium version also gives you access to the following features:', 'restaurant-reservations' ) . '</strong></p>
			<ul class="rtb-dashboard-new-footer-one-benefits">
				<li>' . __( 'Multiple Form Layouts', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Custom Booking Fields', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Advanced Email Designer', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Set Table and Seat Restrictions', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Automatic Booking Confirmation', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Bookings Page for Staff', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Export Bookings', 'restaurant-reservations' ) . '</li>
				<li>' . __( 'Email Support', 'restaurant-reservations' ) . '</li>
			</ul>
			<div class="rtb-dashboard-new-footer-one-buttons">
				<a class="rtb-dashboard-new-upgrade-button" href="https://www.fivestarplugins.com/license-payment/?Selected=RTB&Quantity=1&utm_source=rtb_settings&utm_content=' . $section_and_perm_type . '" target="_blank">' . __( 'UPGRADE NOW', 'restaurant-reservations' ) . '</a>
			</div>
		';

		switch ( $section_and_perm_type ) {

			case 'advanced':

				$content = '
					<div class="rtb-settings-preview">
						<h2>' . __( 'Advanced', 'restaurant-reservations' ) . '<span>' . __( 'Premium/Ultimate', 'restaurant-reservations' ) . '</span></h2>
						<p>' . __( 'The advanced options let you set a maximum number of reservations or people, enable automatic confirmation of bookings, configure a view bookings page for your site that staff can use to see upcoming reservations and check people in, and more. The table settings let you create different sections for your restaurant, and then also create individual tables and assign them to specific sections. You can then allow your customers to choose a table when they book and/or manage the tables in the admin.', 'restaurant-reservations' ) . '</p>
						<div class="rtb-settings-preview-images">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/advanced1.png" alt="RTB advanced screenshot one">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/advanced2.png" alt="RTB advanced screenshot two">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/advanced3.png" alt="RTB advanced screenshot three">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/advanced4.png" alt="RTB advanced screenshot four">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'notifications':

				$content = '
					<div class="rtb-settings-preview">
						<h2>' . __( 'Notifications', 'restaurant-reservations' ) . '<span>' . __( 'Premium/Ultimate', 'restaurant-reservations' ) . '</span></h2>
						<p>' . __( 'The email template designer uses the WordPress customizer to let you modify the look and structure of the notification emails. The reminders section allows you to set up reservation reminders - SMS or email - which are sent at a chosen interval before the booking, as well as late arrival notifications - SMS or email - which are sent at a chosen interval after the booking time has passed.', 'restaurant-reservations' ) . '</p>
						<div class="rtb-settings-preview-images">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/notifications1.png" alt="RTB notifications screenshot one">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/notifications2.png" alt="RTB notifications screenshot two">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/notifications3.png" alt="RTB notifications screenshot three">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'payments':

				$content = '
					<div class="rtb-settings-preview">
						<h2>' . __( 'Payments', 'restaurant-reservations' ) . '<span>' . __( 'Ultimate', 'restaurant-reservations' ) . '</span></h2>
						<p>' . __( 'The payment options let you require a deposit for bookings, either via PayPal or Stripe. Deposits can be made conditional on a minimum party size or only for certain days/times.', 'restaurant-reservations' ) . '</p>
						<div class="rtb-settings-preview-images">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/payments1.png" alt="RTB payments screenshot one">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/payments2.png" alt="RTB payments screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'export':

				$content = '
					<div class="rtb-settings-preview">
						<h2>' . __( 'Export', 'restaurant-reservations' ) . '<span>' . __( 'Premium', 'restaurant-reservations' ) . '</span></h2>
						<p>' . __( 'You can export all your bookings to a PDF file, for use by your staff to manage upcoming bookings, for accounting purposes etc.', 'restaurant-reservations' ) . '</p>
						<div class="rtb-settings-preview-images">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/export.png" alt="RTB export screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'labelling':
	
				$content = '
					<div class="rtb-settings-preview">
						<h2>' . __( 'Labelling', 'restaurant-reservations' ) . '<span>' . __( 'Premium', 'restaurant-reservations' ) . '</span></h2>
						<p>' . __( 'The labelling options let you change the wording of the different labels that appear on the front end of the plugin. You can use this to translate them, customize the wording for your purpose, etc.', 'restaurant-reservations' ) . '</p>
						<div class="rtb-settings-preview-images">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/labelling1.png" alt="RTB labelling screenshot one" />
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/labelling2.png" alt="RTB labelling screenshot two" />
						</div>
						' . $premium_features . '
					</div>
				';
	
				break;

			case 'styling':

				$content = '
					<div class="rtb-settings-preview">
						<h2>' . __( 'Styling', 'restaurant-reservations' ) . '<span>' . __( 'Premium', 'restaurant-reservations' ) . '</span></h2>
						<p>' . __( 'The styling options let you choose a booking form layout and modify the colors, font family, font size and borders of the various elements found in the booking form.', 'restaurant-reservations' ) . '</p>
						<div class="rtb-settings-preview-images">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/styling1.png" alt="RTB styling screenshot one">
							<img src="' . RTB_PLUGIN_URL . '/assets/img/premium-screenshots/styling2.png" alt="RTB styling screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;
		}

		return function() use ( $content ) {

			echo wp_kses_post( $content );
		};
	}

	/**
	 * Get options for the party size setting
	 * @since 1.3
	 */
	public function get_party_size_setting_options( $max = true ) {

		$options = array();

		if ( $max ) {
			$options[''] = __( 'Any size', 'restaurant-reservations' );
		}

		$max = apply_filters( 'rtb_party_size_upper_limit', 100 );

		for ( $i = 1; $i <= $max; $i++ ) {
			$options[$i] = $i;
		}

		return apply_filters( 'rtb_party_size_setting_options', $options );
	}

	/**
	 * Get options for the party select field in the booking form
	 * @since 1.3
	 */
	public function get_form_party_options() {

		$options = array();

		if ( ! empty( $this->get_setting( 'party-blank' ) ) ) {

			$options[] = '';
		}

		$party_size = (int) $this->get_setting( 'party-size' );
		$party_size_min = (int) $this->get_setting( 'party-size-min' );

		$min = apply_filters( 'rtb_party_size_lower_limit', empty( $party_size_min ) ? 1 : (int) $this->get_setting( 'party-size-min' ) );
		$max = apply_filters( 'rtb_party_size_upper_limit', empty( $party_size ) ? 100 : (int) $this->get_setting( 'party-size' ) );

		for ( $i = $min; $i <= $max; $i++ ) {
			$options[$i] = $i;
		}

		return apply_filters( 'rtb_form_party_options', $options );
	}

	/**
	 * Get options for the table select field in the booking form
	 * @since 2.1.7
	 */
	public function get_form_table_options() {

		$options = array();

		$table_sections = json_decode( html_entity_decode( $this->get_setting( 'rtb-table-sections' ) ) );
		$table_sections = is_array( $table_sections ) ? $table_sections : array();

		$tables = json_decode( html_entity_decode( $this->get_setting( 'rtb-tables' ) ) );
		$tables = is_array( $tables ) ? $tables : array();

		foreach ( $tables as $table ) {

			$option = '';
			$table_section_name = '';

			foreach ( $table_sections as $table_section ) { 
				if ( $table_section->section_id == $table->section ) {
					$table_section_name = $table_section->name;
					break;
				}
			}
			
			if( ! empty( $table_section_name ) ) {
				$option = $table->number . ' - ' . $table_section_name . ' (' . $this->get_setting( 'label-table-min' ) . ' ' . $table->min_people . '/' . $this->get_setting( 'label-table-max' ) . ' ' . $table->max_people . ')';
			}
			else {
				$option = $table->number . ' (' . $this->get_setting( 'label-table-min' ) . ' ' . $table->min_people . '/' . $this->get_setting( 'label-table-max' ) . ' ' . $table->max_people . ')';
			}

			$options[ $table->number ] = $option;
		}

		return $options;
	}

	/**
	 * Returns the tables data as $table_number => $table pairs, calls load if necessary
	 * @since 2.1.7
	 */
	public function get_sorted_tables( $datetime = false, $location_id = 0 ) {

		$timeslot = ! empty( $datetime ) ? rtb_get_timeslot( $datetime, $location_id ) : false;

		if ( ! isset( $this->sorted_tables[ $location_id ][ $timeslot ] ) ){

			$this->load_sorted_tables( $timeslot, $location_id );
		}
		
		return $this->sorted_tables[ $location_id ][ $timeslot ];
	}

	/**
	 * Loads the tables data as $table_number => $table pairs
	 * @since 2.1.7
	 */
	public function load_sorted_tables( $timeslot = false, $location_id = 0 ) {

		$location_slug = ! empty( $location_id ) ? get_term_field( 'slug', $location_id ) : false;

		$tables = json_decode( html_entity_decode( $this->get_setting( 'rtb-tables', $location_slug, $timeslot ) ) );
		$tables = is_array( $tables ) ? $tables : array();
		
		$sorted_tables = array();
		foreach ( $tables as $table ) {
			$sorted_tables[ $table->number ] = $table;
		}

		$this->sorted_tables[ $location_id ][ $timeslot ] = $sorted_tables;
	}

	/**
	 * Retrieve form fields
	 *
	 * @param $request rtbBooking Details of a booking request made
	 * @param $args array Associative array of arguments to pass to the field:
	 *  `location` int Location post id
	 * @since 1.3
	 */
	public function get_booking_form_fields( $request = null, $args = array() ) {

		global $rtb_controller;

		// $request will represent a rtbBooking object with the request
		// details when the form is being printed and $_POST data exists
		// to populate the request. All other times $request will just
		// be an empty object
		if ( $request === null ) {
			$request = $rtb_controller->request;
		}

		/**
		 * This array defines the field details and a callback function to
		 * render each field. To customize the form output, modify the
		 * callback functions to point to your custom function. Don't forget
		 * to output an error message in your custom callback function. You
		 * can use rtb_print_form_error( $slug ) to do this.
		 *
		 * In addition to the parameters described below, each fieldset
		 * and field can accept a `classes` array in the callback args since
		 * v1.3. These classes will be appended to the <fieldset> and
		 * <div> elements for each field. A fieldset can also take a
		 * `legend_classes` array in the callback_args which will be
		 * added to the legend element.
		 *
		 * Example:
		 *
		 * 	$fields = array(
		 * 		'fieldset'	=> array(
		 * 			'legend'	=> __( 'My Legend', 'restaurant-reservations' ),
		 * 			'callback_args'	=> array(
		 * 				'classes'		=> array( 'fieldset-class', 'other-fieldset-class' ),
		 * 				'legend_classes	=> array( 'legend-class' ),
		 *			),
		 * 			'fields'	=> array(
		 * 				'my-field'	=> array(
		 * 					...
		 * 					'callback_args'	=> array(
		 * 						'classes'	=> array( 'field-class' ),
		 *					)
		 * 				)
		 * 			)
		 * 		)
		 * 	);
		 *
		 * See /includes/template-functions.php
		 */
		$fields = array(

			// Reservation details fieldset
			'reservation'	=> array(
				'legend'	=> esc_html( $rtb_controller->settings->get_setting( 'label-book-table'  ) ),
				'fields'	=> array(
					'date'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-date' ) ),
						'request_input'	=> empty( $request->request_date ) ? '' : $request->request_date,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'time'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-time' ) ),
						'request_input'	=> empty( $request->request_time ) ? '' : $request->request_time,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'party'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-party' ) ),
						'request_input'	=> empty( $request->party ) ? '' : $request->party,
						'callback'		=> 'rtb_print_form_select_field',
						'callback_args'	=> array(
							'options'	=> $this->get_form_party_options(),
						),
						'required'		=> true,
					),
				),
			),

			// Contact details fieldset
			'rtb-contact'	=> array(
				'legend'	=> esc_html( $rtb_controller->settings->get_setting( 'label-contact-details' ) ),
				'fields'	=> array(
					'name'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-name' ) ),
						'request_input'	=> empty( $request->name ) ? '' : $request->name,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'email'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-email' ) ),
						'request_input'	=> empty( $request->email ) ? '' : $request->email,
						'callback'		=> 'rtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'email',
						),
						'required'		=> true,
					),
					'phone'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-phone' ) ),
						'request_input'	=> empty( $request->phone ) ? '' : $request->phone,
						'callback'		=> 'rtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'tel',
						),
					),
					'add-message'	=> array(
						'title'		=> esc_html( $rtb_controller->settings->get_setting( 'label-add-message' ) ),
						'request_input'	=> '',
						'callback'	=> 'rtb_print_form_message_link',
					),
					'message'		=> array(
						'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-message' ) ),
						'request_input'	=> empty( $request->message ) ? '' : $request->message,
						'callback'		=> 'rtb_print_form_textarea_field',
					),
				),
			),
		);

		// Add a consent request if setting is selected and it's not the admin page
		$require_consent = $rtb_controller->settings->get_setting( 'require-consent' );
		$consent_statement = $rtb_controller->settings->get_setting( 'consent-statement' );
		$privacy_page = $rtb_controller->settings->get_setting( 'privacy-page' );
		if ( !is_admin() && $require_consent && $consent_statement ) {

			if ( $privacy_page && get_post_status( $privacy_page ) !== false ) {
				$consent_statement .= sprintf(' <a href="%s">%s</a>', get_permalink( $privacy_page ), get_the_title( $privacy_page ) );
			}

			$fields['consent'] = array(
				'fields' => array(
					'consent-statement' => array(
						'title' => $consent_statement,
						'request_input' => empty( $request->consent_statement ) ? '' : $request->consent_statement,
						'callback' => 'rtb_print_form_confirm_field',
						'required' => true,
					),
				),
				'order' => 900,
			);
		}

		$enable_tables = $rtb_controller->settings->get_setting( 'enable-tables' );
		$require_table = $rtb_controller->settings->get_setting( 'require-table' );
		if ( $enable_tables ) {

			$fields['reservation']['fields']['table'] = array(
				'title'			=> esc_html( $rtb_controller->settings->get_setting( 'label-table-s' ) ),
				/**
				 * Raw, unprocessed value because processed value is an array
				 */
				'request_input'	=> empty( $request->table ) 
					? '' 
					: (
							array_key_exists( 'rtb-table', $request->raw_input ) 
								? $request->raw_input['rtb-table']
								: ''
						),
				'callback'		=> 'rtb_print_form_select_field',
				'callback_args'	=> array(
					'options'		=> $this->get_form_table_options(),
					'empty_option'	=> true,
					'disabled'		=> true
				),
				'required'		=> $require_table,
				'order'			=> 999
			);
		} elseif ( array_key_exists( 'table', $fields['reservation']['fields'] ) ) {
			unset( $fields['reservation']['fields']['table'] );
		}

		return apply_filters( 'rtb_booking_form_fields', $fields, $request, $args );
	}

	/**
	 * Get required fields
	 *
	 * Filters the fields array to return just those marked required
	 * @since 1.3
	 */
	public function get_required_fields() {

		$required_fields = array();

		$fieldsets = $this->get_booking_form_fields();
		foreach ( $fieldsets as $fieldset ) {
			$required_fields = array_merge( $required_fields, array_filter( $fieldset['fields'], array( $this, 'is_field_required' ) ) );
		}

		return $required_fields;
	}

	/**
	 * Check if a field is required
	 *
	 * @since 1.3
	 */
	public function is_field_required( $field ) {
		return !empty( $field['required'] );
	}

	/**
	 * Render HTML code of descriptions for the template tags
	 * @since 1.2.3
	 */
	public function render_template_tag_descriptions() {

		$descriptions = apply_filters( 'rtb_notification_template_tag_descriptions', array(
				'{user_email}'			=> __( 'Email of the user who made the booking', 'restaurant-reservations' ),
				'{user_name}'			=> __( '* Name of the user who made the booking', 'restaurant-reservations' ),
				'{party}'				=> __( '* Number of people booked', 'restaurant-reservations' ),
				'{date}'				=> __( '* Date and time of the booking', 'restaurant-reservations' ),
				'{phone}'				=> __( 'Phone number if supplied with the request', 'restaurant-reservations' ),
				'{message}'				=> __( 'Message added to the request', 'restaurant-reservations' ),
				'{booking_id}'			=> __( 'The ID of the booking', 'restaurant-reservations' ),
				'{booking_page_link}'	=> __( 'A link to the bookings page on the front-end of the site.', 'restaurant-reservations' ),
				'{booking_url}'			=> __( 'The URL of the bookings page on the front-end of the site.', 'restaurant-reservations' ),
				'{cancel_link}'			=> __( 'A link that a guest can use to cancel their booking if cancellations are enabled', 'restaurant-reservations' ),
				'{cancellation_url}'	=> __( 'The URL of the cancellations link, if cancellations are enabled', 'restaurant-reservations' ),
				'{bookings_link}'		=> __( 'A link to the admin panel showing pending bookings', 'restaurant-reservations' ),
				'{bookings_link_url}'	=> __( 'The URL of the admin panel showing pending bookings', 'restaurant-reservations' ),
				'{confirm_link}'		=> __( 'A link to confirm this booking. Only include this in admin notifications', 'restaurant-reservations' ),
				'{confirm_link_url}'	=> __( 'The URL to confirm this booking. Only include this in admin notifications', 'restaurant-reservations' ),
				'{close_link}'			=> __( 'A link to reject this booking. Only include this in admin notifications', 'restaurant-reservations' ),
				'{close_link_url}'		=> __( 'The URL to reject this booking. Only include this in admin notifications', 'restaurant-reservations' ),
				'{site_name}'			=> __( '* The name of this website', 'restaurant-reservations' ),
				'{site_link}'			=> __( 'A link to the homepage of this website', 'restaurant-reservations' ),
				'{site_link_url}'		=> __( 'The URL of the homepage of this website', 'restaurant-reservations' ),
				'{current_time}'		=> __( 'Current date and time', 'restaurant-reservations' ),
				'{table}'				=> __( 'The table(s) for the booking', 'restaurant-reservations' ),
			)
		);

		$output = '';

		foreach ( $descriptions as $tag => $description ) {
			$output .= '
				<div class="rtb-template-tags-box">
					<strong>' . $tag . '</strong> ' . $description . '
				</div>';
		}

		return $output;
	}

	/**
	 * Sort the schedule exceptions and remove past exceptions before saving
	 *
	 * @since 1.4.6
	 */
	public function clean_schedule_exceptions( $val ) {

		if ( empty( $val['schedule-closed'] ) ) {
			return $val;
		}

		// Sort by date
		$schedule_closed = $val['schedule-closed'];
		usort( $schedule_closed, array( $this, 'sort_by_date' ) );

		// Remove exceptions more than a week old
		$week_ago = time() - 604800;
		foreach( $schedule_closed as $idx => $record ) {
			if( array_key_exists( 'date_range', $record ) && !empty( $record['date_range']['end'] ) )
				$record = new DateTime( $record['date_range']['end'], wp_timezone() );
			elseif( array_key_exists( 'date', $record ) )
				$record = new DateTime( $record['date'], wp_timezone() );

			if ( is_object($record) && $record->format( 'U' ) > $week_ago ) {
				break;
			}
		}
		if ( $idx ) {
			$schedule_closed = array_slice( $schedule_closed, $idx );
		}

		$val['schedule-closed'] = $schedule_closed;

		return $val;
	}

	/**
	 * Sort an associative array by the value's date parameter
	 *
	 * @usedby self::clean_schedule_exceptions()
	 * @since 0.1
	 */
	public function sort_by_date( $a, $b ) {

		if( isset( $a['date'] ) )
			$a = ( new DateTime( $a['date'], wp_timezone() ) )->format( 'U' );
		elseif( isset( $a['date_range'] ) )
			$a = ( new DateTime( $a['date_range']['end'], wp_timezone() ) )->format( 'U' );
		else
			$a = 0;

		if( isset( $b['date'] ) )
			$b = ( new DateTime( $b['date'], wp_timezone() ) )->format( 'U' );
		elseif( isset( $b['date_range'] ) )
			$b = ( new DateTime( $b['date_range']['end'], wp_timezone() ) )->format( 'U' );
		else
			$b = 0;

		return $a - $b;
	}

	/**
	 * Return the table sections as value/name pairs
	 *
	 * @since 2.1.7
	 */
	public function get_table_section_options( $option_slug = false ) {

		$location = ( ! empty( $option_slug ) and substr( $option_slug, 0, 2 ) != 's_' and substr( $option_slug, 0, 2 ) != 'e_' ) ? $option_slug : false;
		$timeslot = ( ! empty( $option_slug ) and empty( $location ) ) ? $option_slug : false;

		$table_sections = ! empty( $this->get_setting( 'rtb-table-sections', $location, $timeslot ) ) ? json_decode( html_entity_decode( $this->get_setting( 'rtb-table-sections', $location, $timeslot ) ) ) : array();
		$table_sections = is_array( $table_sections ) ? $table_sections : array();

		$table_section_options = array();
		foreach ( $table_sections as $table_section ) {
			$table_section_options[ $table_section->section_id ] = $table_section->name;
		}

		return $table_section_options;
	}

	/**
	 * Return the deposit column for tables, if enabled
	 *
	 * @since 2.6.3
	 */
	public function get_table_deposit_column() {

		$table_array = array(
        	'table_deposit' => array(
        		'type'    	=> 'number',
        		'label'   	=> __('Deposit', 'restaurant-reservations' ),
        		'required'  => false
       		)
        );

		return ( $this->get_setting( 'require-deposit' ) and $this->get_setting( 'rtb-deposit-type' ) == 'table' ) ? $table_array : array();
	}

	/**
	 * Return a short description of a timeslot
	 *
	 * @since 2.7.0
	 */
	public function get_timeslot_label( $timeslot_values ) {

		$summary_string = '';

		$abbreviations = array(
			'monday'		=> _x( 'Mo', 'Monday abbreviation', 'restaurant-reservations' ),
			'tuesday'		=> _x( 'Tu', 'Tuesday abbreviation', 'restaurant-reservations' ),
			'wednesday'		=> _x( 'We', 'Wednesday abbreviation', 'restaurant-reservations' ),
			'thursday'		=> _x( 'Th', 'Thursday abbreviation', 'restaurant-reservations' ),
			'friday'		=> _x( 'Fr', 'Friday abbreviation', 'restaurant-reservations' ),
			'saturday'		=> _x( 'Sa', 'Saturday abbreviation', 'restaurant-reservations' ),
			'sunday'		=> _x( 'Su', 'Sunday abbreviation', 'restaurant-reservations' )
		);

		if ( isset( $timeslot_values['weekdays'] ) ) {

			foreach ( $timeslot_values['weekdays'] as $weekday => $value ) {
	
				if ( ! $value ) { continue; }
	
				$summary_string .= $abbreviations[ $weekday ] . ',';
			}
		}
		else {

			if ( isset( $timeslot_values['date_range'] ) ) {
				
				$start_date = new DateTime( $timeslot_values['date_range']['start'], wp_timezone() );
				$end_date = new DateTime( $timeslot_values['date_range']['end'], wp_timezone() );
			}
			else {

				$start_date = $end_date = new Datetime( $timeslot_values['date'], wp_timezone() );
			}

			$summary_string .= $start_date->format( 'd/m' ) . ( $start_date != $end_date ? '-' . $end_date->format( 'd/m' ) : '' );
		}

		$summary_string = trim( $summary_string, ',' ) . ' - ';

		if ( empty( $timeslot_values['time']['start'] ) and empty( $timeslot_values['time']['end'] ) ) {

			$summary_string .= _x( 'All day', 'Brief default description of a scheduling rule when no times are set', 'restaurant-reservations' );
		}
		elseif ( empty( $timeslot_values['time']['start'] ) ) {

			$summary_string .= _x( 'Ends at', 'Brief default description of a scheduling rule when an end time is set but no start time. If the end time is 6pm, it will read: Ends at 6pm', 'restaurant-reservations' ) . ' ' . $timeslot_values['time']['end'];
		}
		elseif ( empty( $timeslot_values['time']['end'] ) ) {

			$summary_string .= _x( 'Starts at', 'Brief default description of a scheduling rule when a start time is set but no end time. If the start time is 6pm, it will read: Starts at 6pm', 'restaurant-reservations' ) . ' ' . $timeslot_values['time']['start'];
		}
		else {

			$summary_string .= $timeslot_values['time']['start'] . _x( '&mdash;', 'Separator between times of a scheduling rule', 'restaurant-reservations' ) . $timeslot_values['time']['end'];
		}

		return $summary_string;
	}

	/**
	 * If multiple location or timeslot options exist, adds in 
	 * location- and timeslot-specific settings for a number of different settings,
	 * and makes all settings conditional on the settings toggle box.
	 *
	 * @since 2.7.0
	 */
	public function maybe_add_location_and_timeslot_settings( $sap ) {
		global $rtb_controller;

		if ( empty( $this->location_options ) and empty( $this->timeslot_options ) ) { return $sap; }

		$tabs_to_modify = array( 'rtb-schedule-tab', 'rtb-advanced-tab' );

		foreach ( $sap->pages['rtb-settings']->sections as $key => $section ) {
			
			if ( property_exists( $section, 'tab' ) and ! in_array( $section->tab, $tabs_to_modify ) ) { continue; }

			foreach ( $section->settings as $setting_key => $setting ) {

				// add get/set to utilize method chaining
				$sap->pages['rtb-settings']->sections[ $key ]->settings[ $setting_key ]->setting_type = $section->tab == 'rtb-advanced-tab' ? array( 'location', 'scheduling_rule' ) : array( 'location' );
				$sap->pages['rtb-settings']->sections[ $key ]->settings[ $setting_key ]->setting_type_value = $section->tab == 'rtb-advanced-tab' ? array( false, false ) : array( false );

				$sap->pages['rtb-settings']->sections[ $key ]->settings[ $setting_key ]->set_setting_type_display();
			}
		}

		// Translateable strings for scheduler components
		$scheduler_strings = array(
			'add_rule'			=> __( 'Add new scheduling rule', 'restaurant-reservations' ),
			'weekly'			=> _x( 'Weekly', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'monthly'			=> _x( 'Monthly', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'date'				=> _x( 'Date', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'date_range'		=> _x( 'Date Range', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'weekdays'			=> _x( 'Days of the week', 'Label for selecting days of the week in a scheduling rule', 'restaurant-reservations' ),
			'month_weeks'		=> _x( 'Weeks of the month', 'Label for selecting weeks of the month in a scheduling rule', 'restaurant-reservations' ),
			'date_label'		=> _x( 'Date', 'Label to select a date for a scheduling rule', 'restaurant-reservations' ),
			'time_label'		=> _x( 'Time', 'Label to select a time slot for a scheduling rule', 'restaurant-reservations' ),
			'allday'			=> _x( 'All day', 'Label to set a scheduling rule to last all day', 'restaurant-reservations' ),
			'start'				=> _x( 'Start', 'Label for the starting time of a scheduling rule', 'restaurant-reservations' ),
			'end'				=> _x( 'End', 'Label for the ending time of a scheduling rule', 'restaurant-reservations' ),
			'set_time_prompt'	=> _x( 'All day long. Want to %sset a time slot%s?', 'Prompt displayed when a scheduling rule is set without any time restrictions', 'restaurant-reservations' ),
			'toggle'			=> _x( 'Open and close this rule', 'Toggle a scheduling rule open and closed', 'restaurant-reservations' ),
			'delete'			=> _x( 'Delete rule', 'Delete a scheduling rule', 'restaurant-reservations' ),
			'delete_schedule'	=> __( 'Delete scheduling rule', 'restaurant-reservations' ),
			'never'				=> _x( 'Never', 'Brief default description of a scheduling rule when no weekdays or weeks are included in the rule', 'restaurant-reservations' ),
			'weekly_always'	=> _x( 'Every day', 'Brief default description of a scheduling rule when all the weekdays/weeks are included in the rule', 'restaurant-reservations' ),
			'monthly_weekdays'	=> _x( '%s on the %s week of the month', 'Brief default description of a scheduling rule when some weekdays are included on only some weeks of the month. %s should be left alone and will be replaced by a comma-separated list of days and weeks in the following format: M, T, W on the first, second week of the month', 'restaurant-reservations' ),
			'monthly_weeks'		=> _x( '%s week of the month', 'Brief default description of a scheduling rule when some weeks of the month are included but all or no weekdays are selected. %s should be left alone and will be replaced by a comma-separated list of weeks in the following format: First, second week of the month', 'restaurant-reservations' ),
			'all_day'			=> _x( 'All day', 'Brief default description of a scheduling rule when no times are set', 'restaurant-reservations' ),
			'before'			=> _x( 'Ends at', 'Brief default description of a scheduling rule when an end time is set but no start time. If the end time is 6pm, it will read: Ends at 6pm', 'restaurant-reservations' ),
			'after'				=> _x( 'Starts at', 'Brief default description of a scheduling rule when a start time is set but no end time. If the start time is 6pm, it will read: Starts at 6pm', 'restaurant-reservations' ),
			'separator'			=> _x( '&mdash;', 'Separator between times of a scheduling rule', 'restaurant-reservations' ),
			'date_range_from_today' => _x( 'From Today', 'When Date range start date is not set', 'restaurant-reservations' ),
			'date_range_upto_indefinite' => _x( 'Upto Indefinite', 'When Date range end date is not set', 'restaurant-reservations' ),
		);

		foreach ( $this->location_options as $location ) {

			if ( empty( $location['value'] ) ) { continue; }

			$scheduler_strings['all_day'] = _x( 'All day', 'Brief default description of a scheduling rule when no times are set', 'restaurant-reservations' );

			$sap->add_setting(
				'rtb-settings',
				'rtb-schedule',
				'scheduler',
				array(
					'id'					=> $location['slug'] . '-schedule-open',
					'title'					=> __( 'Schedule', 'restaurant-reservations' ),
					'description'			=> __( 'Define the weekly schedule during which you accept bookings.', 'restaurant-reservations' ),
					'weekdays'				=> array(
						'monday'				=> _x( 'Mo', 'Monday abbreviation', 'restaurant-reservations' ),
						'tuesday'				=> _x( 'Tu', 'Tuesday abbreviation', 'restaurant-reservations' ),
						'wednesday'				=> _x( 'We', 'Wednesday abbreviation', 'restaurant-reservations' ),
						'thursday'				=> _x( 'Th', 'Thursday abbreviation', 'restaurant-reservations' ),
						'friday'				=> _x( 'Fr', 'Friday abbreviation', 'restaurant-reservations' ),
						'saturday'				=> _x( 'Sa', 'Saturday abbreviation', 'restaurant-reservations' ),
						'sunday'				=> _x( 'Su', 'Sunday abbreviation', 'restaurant-reservations' )
					),
					'time_format'			=> $this->get_setting( 'time-format' ),
					'date_format'			=> $this->get_setting( 'date-format' ),
					'disable_weeks'			=> true,
					'disable_date'			=> true,
					'disable_date_range'	=> true,
					'strings' 				=> $scheduler_strings,
					'setting_type' 			=> 'location',
					'setting_type_value'	=> $location['value'],
				)
			);

			$scheduler_strings['all_day'] = _x( 'Closed all day', 'Brief default description of a scheduling exception when no times are set', 'restaurant-reservations' );

			$sap->add_setting(
				'rtb-settings',
				'rtb-schedule',
				'scheduler',
				array(
					'id'					=> $location['slug'] . '-schedule-closed',
					'title'					=> __( 'Exceptions', 'restaurant-reservations' ),
					'description'			=> __( "Define special opening hours for holidays, events or other needs. Leave the time empty if you're closed all day.", 'restaurant-reservations' ),
					'time_format'			=> esc_attr( $this->get_setting( 'time-format' ) ),
					'date_format'			=> esc_attr( $this->get_setting( 'date-format' ) ),
					'disable_weekdays'		=> true,
					'disable_weeks'			=> true,
					'strings' 				=> $scheduler_strings,
					'setting_type' 			=> 'location',
					'setting_type_value'	=> $location['value'],
				)
			);
		}

		$max_reservation_options = array();
		$max_reservations_upper_limit = apply_filters( 'rtb-max-reservations-upper-limit', 100 );

		for ( $i = 1; $i <= $max_reservations_upper_limit; $i++ ) {

			$max_reservation_options[$i] = $i;
		}

		$max_people_options = array();
		$max_people_upper_limit = apply_filters( 'rtb-max-people-upper-limit', 400 );

		for ( $i = 1; $i <= $max_people_upper_limit; $i++ ) {

			$max_people_options[$i] = $i;
		}

		foreach ( array_merge( $this->location_options, $this->timeslot_options ) as $option ) {

			if ( empty( $option['value'] ) ) { continue; }

			$sap->add_setting(
				'rtb-settings',
				'rtb-seat-assignments',
				'select',
				array(
					'id'					=> $option['slug'] . '-rtb-max-tables-count',
					'title'					=> __( 'Max Reservations', 'restaurant-reservations' ),
					'description'			=> __( 'How many reservations, if enabled, should be allowed at the same time at this location? Set dining block length to change how long a meal typically lasts.', 'restaurant-reservations' ),
					'options'				=> $max_reservation_options,
					'setting_type' 			=> $option['type'],
					'setting_type_value'	=> $option['value'],
				)
			);
		
			$sap->add_setting(
				'rtb-settings',
				'rtb-seat-assignments',
				'select',
				array(
					'id'      				=> $option['slug'] . '-rtb-max-people-count',
					'title'     			=> __( 'Max People', 'restaurant-reservations' ),
					'description'     		=> __( 'How many people, if enabled, should be allowed to be present at this restaurant location at the same time? Set dining block length to change how long a meal typically lasts. May not work correctly if max reservations is set.', 'restaurant-reservations' ),
					'options'				=> $max_people_options,
					'setting_type' 			=> $option['type'],
					'setting_type_value'	=> $option['value'],
				)
			);

			$sap->add_setting(
			    'rtb-settings',
			    'rtb-table-assignments',
			    'infinite_table',
			    array(
			        'id'      => $option['slug'] . '-rtb-table-sections',
			        'title'     => __( 'Sections', 'restaurant-reservations' ),
			        'add_label'   => __( 'Add Section', 'restaurant-reservations' ),
			        'del_label'   => __( 'Delete', 'restaurant-reservations' ),
			        'description' => __( 'Use this area to sections for your tables. These can help your guests to book a table in their preferred area.', 'restaurant-reservations' ),
			        'setting_type' 			=> $option['type'],
					'setting_type_value'	=> $option['value'],
			        'fields'    => array(
			          'section_id' => array(
			            'type'    => 'id',
			            'label'   => __('Section ID', 'restaurant-reservations' ),
			            'required'  => true
			          ),
			          'name' => array(
			            'type'    => 'text',
			            'label'   => __('Section Name', 'restaurant-reservations' ),
			            'required'  => true
			          ),
			          'description' => array(
			            'type'    => 'textarea',
			            'label'   => __('Description', 'restaurant-reservations' ),
			            'required'  => true
			          )
			        ),
			    )
			);
			
			$sap->add_setting(
			    'rtb-settings',
			    'rtb-table-assignments',
			    'infinite_table',
			    array(
			        'id'      => $option['slug'] . '-rtb-tables',
			        'title'     => __( 'Tables', 'restaurant-reservations' ),
			        'add_label'   => __( 'Add Table', 'restaurant-reservations' ),
			        'del_label'   => __( 'Delete', 'restaurant-reservations' ),
			        'description' => __( 'Use this area to create tables that can each be customized. This information will be used to let customers select a table that meets their requirements (party size, date/time available).', 'restaurant-reservations' ),
			        'setting_type' 			=> $option['type'],
					'setting_type_value'	=> $option['value'],
					'fields'    => array_merge(
						array(
			          		'number' => array(
			          			'type'    => 'text',
			          			'label'   => __('Table Number', 'restaurant-reservations' ),
			          			'required'  => true
			          		),
			          		'min_people' => array(
			            		'type'    => 'number',
			            		'label'   => __('Min. People', 'restaurant-reservations' ),
			            		'required'  => true
			          		),
			          		'max_people' => array(
			          			'type'    => 'number',
			          			'label'   => __('Max. People', 'restaurant-reservations' ),
			          			'required'  => true
			         		),
			        		'section' => array(
			            		'type'    => 'select',
			            		'label'   => __('Section', 'restaurant-reservations' ),
			            		'required'  => false,
			            		'options'   => $this->get_table_section_options( $option['slug'] )
			          		),
			        		'combinations' => array(
			            		'type'    => 'text',
			            		'label'   => __('Combines With', 'restaurant-reservations' ),
			            		'required'  => false
			          		)
			        	),
			 			$this->get_table_deposit_column()
			      	)
			    )
			);
		}

		return $sap;
	}

}
} // endif;
