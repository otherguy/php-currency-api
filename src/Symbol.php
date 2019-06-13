<?php namespace Otherguy\Currency;

use ReflectionClass;
use ReflectionException;

/**
 * Class Symbol
 *
 * @package Otherguy\Currency
 */
class Symbol
{
    public const AED = 'AED'; // United Arab Emirates Dirham
    public const AFN = 'AFN'; // Afghan Afghani
    public const ALL = 'ALL'; // Albanian Lek
    public const AMD = 'AMD'; // Armenian Dram
    public const ANG = 'ANG'; // Netherlands Antillean Guilder
    public const AOA = 'AOA'; // Angolan Kwanza
    public const ARS = 'ARS'; // Argentine Peso
    public const AUD = 'AUD'; // Australian Dollar
    public const AWG = 'AWG'; // Aruban Florin
    public const AZN = 'AZN'; // Azerbaijani Manat
    public const BAM = 'BAM'; // Bosnia Herzegovina Convertible Mark
    public const BBD = 'BBD'; // Barbadian Dollar
    public const BDT = 'BDT'; // Bangladeshi Taka
    public const BGN = 'BGN'; // Bulgarian Lev
    public const BHD = 'BHD'; // Bahraini Dinar
    public const BIF = 'BIF'; // Burundian Franc
    public const BMD = 'BMD'; // Bermudan Dollar
    public const BND = 'BND'; // Brunei Dollar
    public const BOB = 'BOB'; // Bolivian Boliviano
    public const BRL = 'BRL'; // Brazilian Real
    public const BSD = 'BSD'; // Bahamian Dollar
    public const BTC = 'BTC'; // Bitcoin
    public const BTN = 'BTN'; // Bhutanese Ngultrum
    public const BWP = 'BWP'; // Botswanan Pula
    public const BYR = 'BYR'; // Belarusian Ruble
    public const BYN = 'BYN'; // New Belarusian Ruble
    public const BZD = 'BZD'; // Belize Dollar
    public const CAD = 'CAD'; // Canadian Dollar
    public const CDF = 'CDF'; // Congolese Franc
    public const CHF = 'CHF'; // Swiss Franc
    public const CLF = 'CLF'; // Chilean Unit of Account
    public const CLP = 'CLP'; // Chilean Peso
    public const CNY = 'CNY'; // Chinese Yuan
    public const COP = 'COP'; // Colombian Peso
    public const CRC = 'CRC'; // Costa Rican Colón
    public const CUC = 'CUC'; // Cuban Convertible Peso
    public const CUP = 'CUP'; // Cuban Peso
    public const CVE = 'CVE'; // Cape Verdean Escudo
    public const CZK = 'CZK'; // Czech Republic Koruna
    public const DJF = 'DJF'; // Djiboutian Franc
    public const DKK = 'DKK'; // Danish Krone
    public const DOP = 'DOP'; // Dominican Peso
    public const DZD = 'DZD'; // Algerian Dinar
    public const EGP = 'EGP'; // Egyptian Pound
    public const ERN = 'ERN'; // Eritrean Nakfa
    public const ETB = 'ETB'; // Ethiopian Birr
    public const EUR = 'EUR'; // Euro
    public const FJD = 'FJD'; // Fijian Dollar
    public const FKP = 'FKP'; // Falkland Islands Pound
    public const GBP = 'GBP'; // British Pound Sterling
    public const GEL = 'GEL'; // Georgian Lari
    public const GGP = 'GGP'; // Guernsey Pound
    public const GHS = 'GHS'; // Ghanaian Cedi
    public const GIP = 'GIP'; // Gibraltar Pound
    public const GMD = 'GMD'; // Gambian Dalasi
    public const GNF = 'GNF'; // Guinean Franc
    public const GTQ = 'GTQ'; // Guatemalan Quetzal
    public const GYD = 'GYD'; // Guyanaese Dollar
    public const HKD = 'HKD'; // Hong Kong Dollar
    public const HNL = 'HNL'; // Honduran Lempira
    public const HRK = 'HRK'; // Croatian Kuna
    public const HTG = 'HTG'; // Haitian Gourde
    public const HUF = 'HUF'; // Hungarian Forint
    public const IDR = 'IDR'; // Indonesian Rupiah
    public const ILS = 'ILS'; // Israeli New Sheqel
    public const IMP = 'IMP'; // Manx pound
    public const INR = 'INR'; // Indian Rupee
    public const IQD = 'IQD'; // Iraqi Dinar
    public const IRR = 'IRR'; // Iranian Rial
    public const ISK = 'ISK'; // Icelandic Króna
    public const JEP = 'JEP'; // Jersey Pound
    public const JMD = 'JMD'; // Jamaican Dollar
    public const JOD = 'JOD'; // Jordanian Dinar
    public const JPY = 'JPY'; // Japanese Yen
    public const KES = 'KES'; // Kenyan Shilling
    public const KGS = 'KGS'; // Kyrgystani Som
    public const KHR = 'KHR'; // Cambodian Riel
    public const KMF = 'KMF'; // Comorian Franc
    public const KPW = 'KPW'; // North Korean Won
    public const KRW = 'KRW'; // South Korean Won
    public const KWD = 'KWD'; // Kuwaiti Dinar
    public const KYD = 'KYD'; // Cayman Islands Dollar
    public const KZT = 'KZT'; // Kazakhstani Tenge
    public const LAK = 'LAK'; // Laotian Kip
    public const LBP = 'LBP'; // Lebanese Pound
    public const LKR = 'LKR'; // Sri Lankan Rupee
    public const LRD = 'LRD'; // Liberian Dollar
    public const LSL = 'LSL'; // Lesotho Loti
    public const LTL = 'LTL'; // Lithuanian Litas
    public const LVL = 'LVL'; // Latvian Lats
    public const LYD = 'LYD'; // Libyan Dinar
    public const MAD = 'MAD'; // Moroccan Dirham
    public const MDL = 'MDL'; // Moldovan Leu
    public const MGA = 'MGA'; // Malagasy Ariary
    public const MKD = 'MKD'; // Macedonian Denar
    public const MMK = 'MMK'; // Myanma Kyat
    public const MNT = 'MNT'; // Mongolian Tugrik
    public const MOP = 'MOP'; // Macanese Pataca
    public const MRO = 'MRO'; // Mauritanian Ouguiya
    public const MUR = 'MUR'; // Mauritian Rupee
    public const MVR = 'MVR'; // Maldivian Rufiyaa
    public const MWK = 'MWK'; // Malawian Kwacha
    public const MXN = 'MXN'; // Mexican Peso
    public const MYR = 'MYR'; // Malaysian Ringgit
    public const MZN = 'MZN'; // Mozambican Metical
    public const NAD = 'NAD'; // Namibian Dollar
    public const NGN = 'NGN'; // Nigerian Naira
    public const NIO = 'NIO'; // Nicaraguan Córdoba
    public const NOK = 'NOK'; // Norwegian Krone
    public const NPR = 'NPR'; // Nepalese Rupee
    public const NZD = 'NZD'; // New Zealand Dollar
    public const OMR = 'OMR'; // Omani Rial
    public const PAB = 'PAB'; // Panamanian Balboa
    public const PEN = 'PEN'; // Peruvian Nuevo Sol
    public const PGK = 'PGK'; // Papua New Guinean Kina
    public const PHP = 'PHP'; // Philippine Peso
    public const PKR = 'PKR'; // Pakistani Rupee
    public const PLN = 'PLN'; // Polish Zloty
    public const PYG = 'PYG'; // Paraguayan Guarani
    public const QAR = 'QAR'; // Qatari Rial
    public const RON = 'RON'; // Romanian Leu
    public const RSD = 'RSD'; // Serbian Dinar
    public const RUB = 'RUB'; // Russian Ruble
    public const RWF = 'RWF'; // Rwandan Franc
    public const SAR = 'SAR'; // Saudi Riyal
    public const SBD = 'SBD'; // Solomon Islands Dollar
    public const SCR = 'SCR'; // Seychellois Rupee
    public const SDG = 'SDG'; // Sudanese Pound
    public const SEK = 'SEK'; // Swedish Krona
    public const SGD = 'SGD'; // Singapore Dollar
    public const SHP = 'SHP'; // Saint Helena Pound
    public const SLL = 'SLL'; // Sierra Leonean Leone
    public const SOS = 'SOS'; // Somali Shilling
    public const SRD = 'SRD'; // Surinamese Dollar
    public const STD = 'STD'; // São Tomé and Príncipe Dobra
    public const SVC = 'SVC'; // Salvadoran Colón
    public const SYP = 'SYP'; // Syrian Pound
    public const SZL = 'SZL'; // Swazi Lilangeni
    public const THB = 'THB'; // Thai Baht
    public const TJS = 'TJS'; // Tajikistani Somoni
    public const TMT = 'TMT'; // Turkmenistani Manat
    public const TND = 'TND'; // Tunisian Dinar
    public const TOP = 'TOP'; // Tongan Paʻanga
    public const TRY = 'TRY'; // Turkish Lira
    public const TTD = 'TTD'; // Trinidad and Tobago Dollar
    public const TWD = 'TWD'; // New Taiwan Dollar
    public const TZS = 'TZS'; // Tanzanian Shilling
    public const UAH = 'UAH'; // Ukrainian Hryvnia
    public const UGX = 'UGX'; // Ugandan Shilling
    public const USD = 'USD'; // United States Dollar
    public const UYU = 'UYU'; // Uruguayan Peso
    public const UZS = 'UZS'; // Uzbekistan Som
    public const VEF = 'VEF'; // Venezuelan Bolívar Fuerte
    public const VND = 'VND'; // Vietnamese Dong
    public const VUV = 'VUV'; // Vanuatu Vatu
    public const WST = 'WST'; // Samoan Tala
    public const XAF = 'XAF'; // CFA Franc BEAC
    public const XAG = 'XAG'; // Silver
    public const XAU = 'XAU'; // Gold
    public const XCD = 'XCD'; // East Caribbean Dollar
    public const XDR = 'XDR'; // Special Drawing Rights
    public const XOF = 'XOF'; // CFA Franc BCEAO
    public const XPF = 'XPF'; // CFP Franc
    public const YER = 'YER'; // Yemeni Rial
    public const ZAR = 'ZAR'; // South African Rand
    public const ZMW = 'ZMW'; // Zambian Kwacha
    public const ZWL = 'ZWL'; // Zimbabwean Dollar

    protected static $currencyNames = [
      'AED' => 'United Arab Emirates Dirham',
      'AFN' => 'Afghan Afghani',
      'ALL' => 'Albanian Lek',
      'AMD' => 'Armenian Dram',
      'ANG' => 'Netherlands Antillean Guilder',
      'AOA' => 'Angolan Kwanza',
      'ARS' => 'Argentine Peso',
      'AUD' => 'Australian Dollar',
      'AWG' => 'Aruban Florin',
      'AZN' => 'Azerbaijani Manat',
      'BAM' => 'Bosnia Herzegovina Convertible Mark',
      'BBD' => 'Barbadian Dollar',
      'BDT' => 'Bangladeshi Taka',
      'BGN' => 'Bulgarian Lev',
      'BHD' => 'Bahraini Dinar',
      'BIF' => 'Burundian Franc',
      'BMD' => 'Bermudan Dollar',
      'BND' => 'Brunei Dollar',
      'BOB' => 'Bolivian Boliviano',
      'BRL' => 'Brazilian Real',
      'BSD' => 'Bahamian Dollar',
      'BTC' => 'Bitcoin',
      'BTN' => 'Bhutanese Ngultrum',
      'BWP' => 'Botswanan Pula',
      'BYR' => 'Belarusian Ruble',
      'BYN' => 'New Belarusian Ruble',
      'BZD' => 'Belize Dollar',
      'CAD' => 'Canadian Dollar',
      'CDF' => 'Congolese Franc',
      'CHF' => 'Swiss Franc',
      'CLF' => 'Chilean Unit of Account',
      'CLP' => 'Chilean Peso',
      'CNY' => 'Chinese Yuan',
      'COP' => 'Colombian Peso',
      'CRC' => 'Costa Rican Colón',
      'CUC' => 'Cuban Convertible Peso',
      'CUP' => 'Cuban Peso',
      'CVE' => 'Cape Verdean Escudo',
      'CZK' => 'Czech Republic Koruna',
      'DJF' => 'Djiboutian Franc',
      'DKK' => 'Danish Krone',
      'DOP' => 'Dominican Peso',
      'DZD' => 'Algerian Dinar',
      'EGP' => 'Egyptian Pound',
      'ERN' => 'Eritrean Nakfa',
      'ETB' => 'Ethiopian Birr',
      'EUR' => 'Euro',
      'FJD' => 'Fijian Dollar',
      'FKP' => 'Falkland Islands Pound',
      'GBP' => 'British Pound Sterling',
      'GEL' => 'Georgian Lari',
      'GGP' => 'Guernsey Pound',
      'GHS' => 'Ghanaian Cedi',
      'GIP' => 'Gibraltar Pound',
      'GMD' => 'Gambian Dalasi',
      'GNF' => 'Guinean Franc',
      'GTQ' => 'Guatemalan Quetzal',
      'GYD' => 'Guyanaese Dollar',
      'HKD' => 'Hong Kong Dollar',
      'HNL' => 'Honduran Lempira',
      'HRK' => 'Croatian Kuna',
      'HTG' => 'Haitian Gourde',
      'HUF' => 'Hungarian Forint',
      'IDR' => 'Indonesian Rupiah',
      'ILS' => 'Israeli New Sheqel',
      'IMP' => 'Manx pound',
      'INR' => 'Indian Rupee',
      'IQD' => 'Iraqi Dinar',
      'IRR' => 'Iranian Rial',
      'ISK' => 'Icelandic Króna',
      'JEP' => 'Jersey Pound',
      'JMD' => 'Jamaican Dollar',
      'JOD' => 'Jordanian Dinar',
      'JPY' => 'Japanese Yen',
      'KES' => 'Kenyan Shilling',
      'KGS' => 'Kyrgystani Som',
      'KHR' => 'Cambodian Riel',
      'KMF' => 'Comorian Franc',
      'KPW' => 'North Korean Won',
      'KRW' => 'South Korean Won',
      'KWD' => 'Kuwaiti Dinar',
      'KYD' => 'Cayman Islands Dollar',
      'KZT' => 'Kazakhstani Tenge',
      'LAK' => 'Laotian Kip',
      'LBP' => 'Lebanese Pound',
      'LKR' => 'Sri Lankan Rupee',
      'LRD' => 'Liberian Dollar',
      'LSL' => 'Lesotho Loti',
      'LTL' => 'Lithuanian Litas',
      'LVL' => 'Latvian Lats',
      'LYD' => 'Libyan Dinar',
      'MAD' => 'Moroccan Dirham',
      'MDL' => 'Moldovan Leu',
      'MGA' => 'Malagasy Ariary',
      'MKD' => 'Macedonian Denar',
      'MMK' => 'Myanma Kyat',
      'MNT' => 'Mongolian Tugrik',
      'MOP' => 'Macanese Pataca',
      'MRO' => 'Mauritanian Ouguiya',
      'MUR' => 'Mauritian Rupee',
      'MVR' => 'Maldivian Rufiyaa',
      'MWK' => 'Malawian Kwacha',
      'MXN' => 'Mexican Peso',
      'MYR' => 'Malaysian Ringgit',
      'MZN' => 'Mozambican Metical',
      'NAD' => 'Namibian Dollar',
      'NGN' => 'Nigerian Naira',
      'NIO' => 'Nicaraguan Córdoba',
      'NOK' => 'Norwegian Krone',
      'NPR' => 'Nepalese Rupee',
      'NZD' => 'New Zealand Dollar',
      'OMR' => 'Omani Rial',
      'PAB' => 'Panamanian Balboa',
      'PEN' => 'Peruvian Nuevo Sol',
      'PGK' => 'Papua New Guinean Kina',
      'PHP' => 'Philippine Peso',
      'PKR' => 'Pakistani Rupee',
      'PLN' => 'Polish Zloty',
      'PYG' => 'Paraguayan Guarani',
      'QAR' => 'Qatari Rial',
      'RON' => 'Romanian Leu',
      'RSD' => 'Serbian Dinar',
      'RUB' => 'Russian Ruble',
      'RWF' => 'Rwandan Franc',
      'SAR' => 'Saudi Riyal',
      'SBD' => 'Solomon Islands Dollar',
      'SCR' => 'Seychellois Rupee',
      'SDG' => 'Sudanese Pound',
      'SEK' => 'Swedish Krona',
      'SGD' => 'Singapore Dollar',
      'SHP' => 'Saint Helena Pound',
      'SLL' => 'Sierra Leonean Leone',
      'SOS' => 'Somali Shilling',
      'SRD' => 'Surinamese Dollar',
      'STD' => 'São Tomé and Príncipe Dobra',
      'SVC' => 'Salvadoran Colón',
      'SYP' => 'Syrian Pound',
      'SZL' => 'Swazi Lilangeni',
      'THB' => 'Thai Baht',
      'TJS' => 'Tajikistani Somoni',
      'TMT' => 'Turkmenistani Manat',
      'TND' => 'Tunisian Dinar',
      'TOP' => 'Tongan Paʻanga',
      'TRY' => 'Turkish Lira',
      'TTD' => 'Trinidad and Tobago Dollar',
      'TWD' => 'New Taiwan Dollar',
      'TZS' => 'Tanzanian Shilling',
      'UAH' => 'Ukrainian Hryvnia',
      'UGX' => 'Ugandan Shilling',
      'USD' => 'United States Dollar',
      'UYU' => 'Uruguayan Peso',
      'UZS' => 'Uzbekistan Som',
      'VEF' => 'Venezuelan Bolívar Fuerte',
      'VND' => 'Vietnamese Dong',
      'VUV' => 'Vanuatu Vatu',
      'WST' => 'Samoan Tala',
      'XAF' => 'CFA Franc BEAC',
      'XAG' => 'Silver',
      'XAU' => 'Gold',
      'XCD' => 'East Caribbean Dollar',
      'XDR' => 'Special Drawing Rights',
      'XOF' => 'CFA Franc BCEAO',
      'XPF' => 'CFP Franc',
      'YER' => 'Yemeni Rial',
      'ZAR' => 'South African Rand',
      'ZMW' => 'Zambian Kwacha',
      'ZWL' => 'Zimbabwean Dollar'
    ];

    /**
     * @return array
     *
     * @throws ReflectionException
     */
    public static function all(): array
    {
        return array_values((new ReflectionClass(static::class))->getConstants());
    }

    /**
     * Retrieve the name of a currency by its symbol.
     *
     * @param  mixed $currency The currency symbol.
     *
     * @return string
     */
    public static function name(string $currency): string
    {
      return isset(static::$currencyNames[$currency]) ? static::$currencyNames[$currency] : null;
    }

    /**
     * Retrieve an array of all currencies with their names.
     *
     * @return array
     */
    public static function names(): array
    {
      return static::$currencyNames;
    }
}
