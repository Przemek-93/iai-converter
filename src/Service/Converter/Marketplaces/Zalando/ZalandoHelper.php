<?php

namespace App\Service\Converter\Marketplaces\Zalando;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;
use SimpleXMLElement;

class ZalandoHelper
{
    protected const DEFAULT_LANG_SHORT = 'eng';
    protected const ADDITIONAL_LANG_SHORT = 'pol';

    protected Converter $converterHelper;

    public function __construct(Converter $converterHelper) {
        $this->converterHelper = $converterHelper;
    }

    public function excludeFailureProducts(array &$nameArray, array $xmlProduct): bool
    {
        if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false ||  $xmlProduct['@attributes']['id'] === '19389') {
            return false;
        }

        if ($xmlProduct['category']['@attributes']['id'] === '1214553957' || stripos($xmlProduct['category']['@attributes']['name'], 'walizk')) {
            return false;
        }

        $defaultName = $this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT);
        $defaultNameArray = explode(' ', $defaultName);
        $defaultWord = mb_strtoupper($defaultNameArray[count($defaultNameArray) - 2] . ' ' . end($defaultNameArray));
        if (in_array($defaultWord, $nameArray)) {
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
