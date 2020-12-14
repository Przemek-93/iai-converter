<?php

namespace App\Service\Converter\Marketplaces;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;

class ZalandofeedConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><TBSTOCK></TBSTOCK>';
    protected Converter $converterHelper;

    public function __construct(
        Converter $converterHelper
    ) {
        $this->converterHelper = $converterHelper;
    }

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXML = new SimpleXMLExtended(self::HEADER);
        $file = XmlToArray::createArray($file);
        foreach ($file['offer']['products']['product'] as $xmlProduct) {
            if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false) {
                continue;
            }
            $item = $simpleXML->addChild('ARTICLE');
            $item->addChild('A_NR', $xmlProduct['@attributes']['id']);
            $item->addChild('A_STOCK', $this->converterHelper->getStock($xmlProduct['sizes']['size']['stock']));
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $simpleXML;
    }

}
