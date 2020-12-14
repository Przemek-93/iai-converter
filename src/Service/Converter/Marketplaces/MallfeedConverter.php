<?php

namespace App\Service\Converter\Marketplaces;

use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;

class MallfeedConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><ITEMS></ITEMS>';
    protected const LANG = 'cze';

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXML = new SimpleXMLExtended('<AVAILABILITIES></AVAILABILITIES>');
        $files = XmlToArray::createArray($file);

        foreach ($files['offer']['products']['product'] as $xmlProduct) {
            if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false) {
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
}
