<?php

namespace App\Service\Converter\Marketplaces\Zalando;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;

class ZalandofeedConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><TBSTOCK></TBSTOCK>';

    protected Converter $converterHelper;
    protected zalandoHelper $zalandoHelper;

    public function __construct(
        Converter $converterHelper,
        ZalandoHelper $zalandoHelper
    ) {
        $this->converterHelper = $converterHelper;
        $this->zalandoHelper = $zalandoHelper;
    }

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXML = new SimpleXMLExtended(self::HEADER);
        $file = XmlToArray::createArray($file);
        $nameArray = [];
        foreach ($file['offer']['products']['product'] as $xmlProduct) {
            if (!$this->zalandoHelper->excludeFailureProducts($nameArray, $xmlProduct)) {
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
