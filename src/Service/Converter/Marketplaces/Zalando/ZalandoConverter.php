<?php

namespace App\Service\Converter\Marketplaces\Zalando;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;
use SimpleXMLElement;

class ZalandoConverter implements MarketplaceConverterInterface
{
    protected const HEADER = '<?xml version="1.0" encoding="utf-8"?><TBCATALOG version="1.4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://api.trade-server.net/schema/all_in_one/tbcat_1_4_import.xsd"></TBCATALOG>';
    protected const DEFAULT_LANG_SHORT = 'pol';
    protected const ADDITIONAL_LANG_SHORT = 'eng';
    protected const DEFAULT_LANG = 'x-default';
    protected const ADDITIONAL_LANG = 'en-US';
    protected Converter $converterHelper;

    public function __construct(
        Converter $converterHelper
    ) {
        $this->converterHelper = $converterHelper;
    }

    public function convert(string $file): SimpleXMLExtended
    {
        $simpleXMLMain = new SimpleXMLExtended(self::HEADER);
        $simpleXML = $simpleXMLMain->addChild('PRODUCTDATA');
        $simpleXML->addAttribute('type', 'full');
        $file = XmlToArray::createArray($file);
        foreach ($file['offer']['products']['product'] as $xmlProduct) {
            if (strpos($xmlProduct['@attributes']['code_on_card'], 'BUTY') !== false) {
                continue;
            }
            $item = $simpleXML->addChild('PRODUCT');

            $item->addChild('P_NR', $xmlProduct['@attributes']['id']);

            $pName = $item->addChild('P_NAME');
            $pNameChildFirst = $pName->addChild('VALUE', $this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT));
            $pNameChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
            $pNameChildSecond = $pName->addChild('VALUE', $this->getName($xmlProduct['description'], self::ADDITIONAL_LANG_SHORT, self::DEFAULT_LANG_SHORT));
            $pNameChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);

            $pText = $item->addChild('P_TEXT');
            $pTextChildFirst = $pText->addChild('VALUE',$this->getDescription($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT));
            $pTextChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
            $pTextChildSecond = $pText->addChild('VALUE',$this->getDescription($xmlProduct['description'], self::ADDITIONAL_LANG_SHORT, self::DEFAULT_LANG_SHORT));
            $pTextChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);

            $pBrand = $item->addChild('P_BRAND');
            $pBrand->addAttribute('identifier', 'key');
            $pBrand->addAttribute('key', $xmlProduct['producer']['@attributes']['id']);
            $pBrand->addAttribute('name', $xmlProduct['producer']['@attributes']['name']);

            $pKeywords = $item->addChild('P_KEYWORDS');
            $pKeyword = $pKeywords->addChild('P_KEYWORD');
            $pKeywordChildFirst = $pKeyword->addChild('VALUE',$this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT));
            $pKeywordChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
            $pKeywordChildSecond = $pKeyword->addChild('VALUE',$this->getName($xmlProduct['description'], self::ADDITIONAL_LANG_SHORT, self::DEFAULT_LANG_SHORT));
            $pKeywordChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);

            $pMediaData = $item->addChild('P_MEDIADATA');
            $this->addImages($pMediaData, $xmlProduct['images']['large']['image']);

            $pCategories = $item->addChild('P_CATEGORIES');
            $pCategory = $pCategories->addChild('P_CATEGORY', $xmlProduct['category']['@attributes']['name']);
            $pCategory->addAttribute('type', 'cluster');
            $pCategory->addAttribute('identifier', 'key');
            $pCategory->addAttribute('key', 'clust1');

            $articleData = $item->addChild('ARTICLEDATA');
            $article = $articleData->addChild('ARTICLE');

            $article->addChild('A_NR', $xmlProduct['@attributes']['id']);

            $article->addChild('A_ACTIVE', $this->converterHelper->getStock($xmlProduct['sizes']['size']['stock']) > 0 ? 1 : 0);

            $article->addChild('A_PROD_NR', $xmlProduct['@attributes']['id']);

            $article->addChild('A_STOCK', $this->converterHelper->getStock($xmlProduct['sizes']['size']['stock']));

            // LATER
//            $pComponentData = $item->addChild('P_COMPONENTDATA');
//            $pComponent = $pComponentData->addChild('P_COMPONENT');
//            $pComponent->addAttribute('identifier', 'key');
//            $pComponent->addAttribute('key', '');
//            $pComponent->addAttribute('name', '');
//            $pComponentChildFirst = $pComponent->addChild('VALUE',
//                $xmlProduct['description']['name'][$this->converterHelper->findLanguage($xmlProduct['description']['name'], self::DEFAULT_LANG_SHORT)]['@value']
//            );
//            $pComponentChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
//            $pComponentChildSecond = $pKeyword->addChild('VALUE',
//                $xmlProduct['description']['name'][$this->converterHelper->findLanguage($xmlProduct['description']['long_desc'], self::ADDITIONAL_LANG_SHORT)]['@value']
//            );
//            $pComponentChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $simpleXMLMain;
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

    protected function getDescription(
        array $description,
        string $mainLang,
        string $defaultLang
    ): ?string {
        if (!isset($description['short_desc'])) {
            return null;
        }

        $offset = $this->converterHelper->findLanguage($description['short_desc'], $mainLang, $defaultLang);
        if ($offset) {
            $name = $description['short_desc'][$offset]['@value'];
        }
        if (!$offset) {
            $name = $description['short_desc']['@value'];
        }

        return $name;
    }

    protected function addImages(SimpleXMLElement $element, array $images): void
    {
        foreach ($images as $key => $value) {
            if (isset($value['@attributes'])) {
                $pMedia = $element->addChild('P_MEDIA', $value['@attributes']['url']);
                $pMedia->addAttribute('type', 'image');
                $pMedia->addAttribute('sort', $key);
            }
        }
    }

}
