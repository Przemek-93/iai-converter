<?php

namespace App\Service\Converter\Marketplaces\Zalando;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;
use SimpleXMLElement;

class ZalandoHelper
{
    public const HEADER = '<?xml version="1.0" encoding="utf-8"?><TBCATALOG version="1.4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://api.trade-server.net/schema/all_in_one/tbcat_1_4_import.xsd"></TBCATALOG>';
    public const DEFAULT_LANG_SHORT = 'eng';
    public const ADDITIONAL_LANG_SHORT = 'pol';
    public const DEFAULT_LANG = 'x-default';
    public const ADDITIONAL_LANG = 'pl';
    public const CHANNEL_DE_ID = 'zade';
    public const CHANNEL_PL_ID = 'zapl';
    public const CURRENCY_DE = 'EUR';
    public const CURRENCY_DE_VALUE = 4.40;
    public const CURRENCY_PL = 'PLN';
    public const MATERIAL_ID = '140';
    public const FEMALE_CODES = ['F1', 'FL', 'FA', 'ML', 'FB', '8808', 'P2'];
    public const MALE_CODES = [
        'CPR', 'PC', 'PA', 'S3', '8806', 'S0', 'SK', 'S18', 'SA', 'SL', 'S2', 'P0', 'S1',
        'SR', 'E01', 'ST', 'SW', 'MS', 'SB', 'SN', 'PC', 'RM', 'N4', 'N9', 'SV'
    ];
    public const UNISEX_CODES = ['STL', 'MLW'];
    public const AGE_GROUP = 'adult';
    public const SIZE_GRID = 'UE XS-XL';
    public const SEASON = 'NOOS';
    public const FAILURE_IMAGES = [
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_3.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/2/16925_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/3/4/16943_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/9/4/16949_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/2/16925_2.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/2/4/16942_2.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/3/4/16943_3.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/4/16945_2.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/9/4/16949_2.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_2.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/2/16925_3.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/2/4/16942_4.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/3/4/16943_2.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/4/16945_3.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/9/4/16949_5.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_4.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/4/2/16824_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/2/16825_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/6/2/16826_5.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/2/16925_4.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/2/4/16942_11.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/3/4/16943_4.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/4/16945_5.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/9/4/16949_4.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_5.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/3/2/16823_1.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/5/2/16925_5.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/2/4/16942_8.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/9/4/16949_3.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_6.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/2/4/16942_9.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/0/2/16820_7.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/6/9/53896_7.jpg',
        'https://merlitz.eu/data/gfx/pictures/large/7/9/53897_7.jpg'
    ];
    public const FAILURE_ID = ['19860', '19389', '1214553957', '21412', '21427'];

    protected Converter $converterHelper;

    public function __construct(Converter $converterHelper) {
        $this->converterHelper = $converterHelper;
    }

    public function excludeFailureProducts(array &$nameArray, array $xmlProduct): bool
    {
        if (stripos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false) {
            return false;
        }

        if (in_array($xmlProduct['@attributes']['id'], self::FAILURE_ID, true)) {
            return false;
        }

        if (stripos($xmlProduct['category']['@attributes']['name'], 'walizk')) {
            return false;
        }

        if (in_array($xmlProduct['category']['@attributes']['id'], self::FAILURE_ID, true)) {
            return false;
        }

        $defaultName = $this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT);
        $defaultNameArray = explode(' ', $defaultName);
        $defaultWord = mb_strtoupper($defaultNameArray[count($defaultNameArray) - 2] . ' ' . end($defaultNameArray));
        if (in_array($defaultWord, $nameArray, true)) {
            return false;
        }

        if (stripos($defaultName, 'portfel') !== false) {
            return false;
        }

        $nameArray[] = $defaultWord;

        return true;
    }

    protected function getName(
        array $description,
        string $mainLang,
        string $defaultLang
    ): string {
        $offset = $this->converterHelper->findLanguage($description['name'], $mainLang, $defaultLang);
        if ($offset) {
            $name = $description['name'][$offset]['@value'];
        }

        if (!$offset) {
            $name = $description['name']['@value'];
        }

        return $name;
    }
}
