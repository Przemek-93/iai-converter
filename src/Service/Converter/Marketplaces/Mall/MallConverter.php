<?php

namespace App\Service\Converter\Marketplaces\Mall;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;
use SimpleXMLElement;

class MallConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><ITEMS></ITEMS>';
    protected const LANG = 'cze';
    protected const CURRENCY_VALUE = 6.4516;
    protected Converter $converterHelper;

    public function __construct(
        Converter $converterHelper
    ) {
        $this->converterHelper = $converterHelper;
    }

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXML = new SimpleXMLExtended(self::HEADER);
        $files = XmlToArray::createArray($file);
        foreach ($files['offer']['products']['product'] as $xmlProduct) {
            if ($this->messDetector($xmlProduct)) {
                continue;
            }

            $item = $simpleXML->addChild('ITEM');
            $item->addChild('ID', $xmlProduct['@attributes']['id']);
            $item->addChild('STAGE', 'draft');
            $categoryId = $this->getExternalCategoryById($xmlProduct['category']['@attributes']['id']);
            $item->addChild('CATEGORY_ID', $categoryId);
            $item->addChild('BRAND_ID', 'SOLIER');
            $item->addChild('TITLE', $xmlProduct['description']['name'][$this->converterHelper->findLanguage(
                $xmlProduct['description']['name'], self::LANG, self::LANG)]['@value']
            );
            $item->addChild('SHORTDESC', $xmlProduct['description']['short_desc'][$this->converterHelper->findLanguage(
                $xmlProduct['description']['short_desc'], self::LANG, self::LANG)]['@value']
            );
            $item->addChild('LONGDESC', $xmlProduct['description']['long_desc'][$this->converterHelper->findLanguage(
                $xmlProduct['description']['long_desc'], self::LANG, self::LANG)]['@value']
            );
            $item->addChild('PRIORITY', 3);
            $item->addChild('PACKAGE_SIZE', 'smallbox');
            $item->addChild('BARCODE', $xmlProduct['sizes']['size']['@attributes']['code_producer'] ?? $xmlProduct['sizes']['size']['@attributes']['code']);
            $item->addChild('PRICE', $this->converterHelper->currencyExchange(self::CURRENCY_VALUE, $xmlProduct['price']['@attributes']['net']));
            $item->addChild('VAT', 21);
            $item->addChild('RRP', $this->converterHelper->currencyExchange(self::CURRENCY_VALUE, $xmlProduct['price']['@attributes']['gross']));
            $this->addSpecificParameterByCategory($item, $xmlProduct['category']['@attributes']['id'], $categoryId);
            $this->addMedia($xmlProduct['images']['large']['image'], $item);
            $item->addChild('DELIVERY_DELAY', 0);
            $item->addChild('FREE_DELIVERY', 'false');
        }

        if (ob_get_contents()) {
            ob_end_clean();
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

    protected function getExternalCategoryById(string $categoryId): string
    {
        switch ($categoryId) {
            case '1214553918':
            case '1214553957':
            case '1214553956':
                $externalCategoryId = 'NK034';
                break;
            case '1214553958':
            case '1214553902':
            case '1214553905':
            case '1214553965':
                $externalCategoryId = 'NM008';
                break;
            case '1214553903':
                $externalCategoryId = 'EG009';
                break;
            case '1214553948':
            case '1214553952':
            case '1214553944':
                $externalCategoryId = 'NM009';
                break;
            default:
                $externalCategoryId = 'NM009';
        }

        return $externalCategoryId;
    }

    protected function addSpecificParameterByCategory(SimpleXMLElement $item, string $internalCategoryId, string $externalCategoryId): void
    {
        switch ($internalCategoryId) {
            case '1214553956' && $externalCategoryId === 'NK034':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'TYPE_OF_COMPLEMENT');
                $param->addChild('VALUE', 'kosmetick?? ta??ka');
                break;
            case '1214553918' && $externalCategoryId === 'NK034':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'LUGGAGES-TRAVELBAGS');
                $param->addChild('VALUE', 'Ano');
                break;
            case '1214553903' && $externalCategoryId === 'EG009':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'MAX_DIAGONAL_NTB');
                $param->addChild('VALUE', '14 / 14,9');
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'TYPE_EG009');
                $param->addChild('VALUE', 'batoh na notebook');
                break;
            case '1214553902' && $externalCategoryId === 'NM008':
            case '1214553905' && $externalCategoryId === 'NM008':
            case '1214553965' && $externalCategoryId === 'NM008':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'MEN_WOMEN');
                $param->addChild('VALUE', 'Unisex, P??nsk??');
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'TYPE_OF_FASHION_ACCESORIES');
                $param->addChild('VALUE', 'ta??ka');
                break;
            case '1214553944' && $externalCategoryId === 'NM009':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'TYPE_OF_FASHION_ACCESORIES');
                $param->addChild('VALUE', 'pen????enka');
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'MEN_WOMEN');
                $param->addChild('VALUE', 'P??nsk??, Unisex');
                break;
            case '1214553948' && $externalCategoryId === 'NM009':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'TYPE_OF_FASHION_ACCESORIES');
                $param->addChild('VALUE', 'ostatn??');
                break;
            case '1214553952' && $externalCategoryId === 'NM009':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'TYPE_OF_FASHION_ACCESORIES');
                $param->addChild('VALUE', 'p??sek, k??andy');
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'MEN_WOMEN');
                $param->addChild('VALUE', 'P??nsk??, Unisex');
                break;
            case '1214553958' && $externalCategoryId === 'NM008':
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'USAGE_FASHION');
                $param->addChild('VALUE', 'p??es rameno');
                $param = $item->addChild('PARAM');
                $param->addChild('NAME', 'MEN_WOMEN');
                $param->addChild('VALUE', 'D??msk??, Unisex');
                break;
        }

    }

    protected function addMedia(array $images, SimpleXMLElement $item): void
    {
        if (count($images) >=  2 && !isset($images['@value'])) {
            $i = 1;
            foreach ($images as $key => $image) {
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
        } else {
            $media = $item->addChild('MEDIA');
            $media->addChild('URL',  $images['@attributes']['url']);
            $media->addChild('MAIN', 'true');
        }
    }
}
