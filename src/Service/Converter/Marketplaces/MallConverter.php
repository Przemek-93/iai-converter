<?php

namespace App\Service\Converter\Marketplaces;

use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;

class MallConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><ITEMS></ITEMS>';
    protected const LANG = 'cze';
    protected const INCORRECT = 'niedostepny';

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXML = new SimpleXMLExtended(self::HEADER);
        $files = XmlToArray::createArray($file);
        foreach ($files['offer']['products']['product'] as $xmlProduct) {
            if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false) {
                continue;
            }

            $item = $simpleXML->addChild('ITEM');
            $item->addChild('ID', $xmlProduct['@attributes']['id']);
            $item->addChild('STAGE', 'draft');

            $category = 'NM008';
//            if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false) {
//                $category = 'NM006';
//            }

            if (
                strpos($xmlProduct['@attributes']['code_on_card'], 'PORTFEL') !== false ||
                strpos($xmlProduct['@attributes']['code_on_card'], 'BILONÓWKA') !== false
            ) {
                $category = 'NM009';
            }

            $item->addChild('CATEGORY_ID', $category);
            $item->addChild('BRAND_ID', $xmlProduct['producer']['@attributes']['name']);
            $item->addChild('TITLE', $xmlProduct['description']['name'][0]['@value']);
            $item->addChild('SHORTDESC', $xmlProduct['description']['short_desc'][0]['@value']);
            $item->addChild('LONGDESC', $xmlProduct['description']['long_desc'][0]['@value']);
            $item->addChild('PRIORITY', 3);
            $item->addChild('PACKAGE_SIZE', 'smallbox');
            $item->addChild('BARCODE', $xmlProduct['sizes']['size']['@attributes']['code_producer'] ?? $xmlProduct['sizes']['size']['@attributes']['code']);
            $item->addChild('PRICE', $xmlProduct['price']['@attributes']['net']);
            $item->addChild('VAT', $xmlProduct['@attributes']['vat']);
            $item->addChild('RRP', $xmlProduct['price']['@attributes']['gross']);

            if (count($xmlProduct['images']['large']['image']) > 2) {
                $i = 1;
                foreach ($xmlProduct['images']['large']['image'] as $key => $image) {
                    $media = $item->addChild('MEDIA');
                    $media->addChild('URL',  $image['@attributes']['url']);
                    if ($key === 0) {
                        $media->addChild('MAIN', 'true');
                        continue;
                    }

                    $media->addChild('MAIN',  'false');

                    if ($i === 5) {
                        break;
                    }
                    $i++;
                }
            }

            $item->addChild('DELIVERY_DELAY', 0);
            $item->addChild('FREE_DELIVERY', 'false');
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $simpleXML;
    }

    private function getParameter($product, $type)
    {
        foreach ($product['parameters'] as $parameters) {
            if (array_key_exists(0, $parameters)) {
                foreach ($parameters as $parameter) {
                    if ($parameter['@attributes']['name'] === $type) {
                        if (isset($parameter['value'])) {
                            return $parameter['value']['@attributes']['name'];
                        } else {
                            return self::INCORRECT;
                        }
                    }
                }
            }

            if ($parameters['@attributes']['name'] === $type) {
                if (isset($parameters['value'])) {
                    return $parameters['value']['@attributes']['name'];
                } else {
                    return self::INCORRECT;
                }
            }
            return self::INCORRECT;
        }
    }
}
