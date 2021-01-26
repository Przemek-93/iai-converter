<?php

namespace App\Service\Converter\Marketplaces\Mall;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;

class MallfeedConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><ITEMS></ITEMS>';
    protected const LANG = 'cze';
    protected Converter $converterHelper;

    public function __construct(
        Converter $converterHelper
    ) {
        $this->converterHelper = $converterHelper;
    }

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXML = new SimpleXMLExtended('<AVAILABILITIES></AVAILABILITIES>');
        $files = XmlToArray::createArray($file);

        foreach ($files['offer']['products']['product'] as $xmlProduct) {
            if ($this->messDetector($xmlProduct)) {
                continue;
            }

            $item = $simpleXML->addChild('AVAILABILITY');
            $item->addChild('ID', $xmlProduct['@attributes']['id']);
            $item->addChild('IN_STOCK', $xmlProduct['sizes']['size']['stock']['@attributes']['quantity'] ?? 0);

            if (!isset($xmlProduct['sizes']['size']['stock']['@attributes']['quantity'])){
                $item->addChild('ACTIVE', 'false');
            }

            if (isset($xmlProduct['sizes']['size']['stock']['@attributes']['quantity'])){
                $item->addChild('ACTIVE', 'true');
            }

        }

        return $simpleXML;
    }

    protected function messDetector(array $xmlProduct): bool
    {
        if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false ||
            $xmlProduct['category']['@attributes']['id'] === '1214553903') {
            return true;
        }

        if (
            $this->converterHelper->findLanguage(
                $xmlProduct['description']['short_desc'], self::LANG, self::LANG) === null
        ) {
            return true;
        }

        if (
            $this->converterHelper->findLanguage(
                $xmlProduct['description']['name'], self::LANG, self::LANG) === null
        ) {
            return true;
        }

        if (
            $this->converterHelper->findLanguage(
                $xmlProduct['description']['long_desc'], self::LANG, self::LANG) === null
        ) {
            return true;
        }

        if (in_array($xmlProduct['@attributes']['id'],
            [
                '16945', '17107', '17108', '17223', '17225',
                '17226', '17228', '17230', '17234', '19069',
                '19085', '19584', '27344'
            ]
        )) {
            return true;
        }

        return false;
    }
}
