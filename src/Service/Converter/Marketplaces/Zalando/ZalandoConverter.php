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
    protected const DEFAULT_LANG_SHORT = 'eng';
    protected const ADDITIONAL_LANG_SHORT = 'pol';
    protected const DEFAULT_LANG = 'x-default';
    protected const ADDITIONAL_LANG = 'pl-PL';
    protected const CHANNEL_DE_ID = 30;
    protected const CHANNEL_PL_ID = 172;
    protected const CURRENCY_DE = 'EUR';
    protected const CURRENCY_DE_VALUE = 4.60;
    protected const CURRENCY_PL = 'PLN';
    protected const MATERIAL_ID = '140';
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
            $this->addNameTag($item, $xmlProduct);
            $this->addTextTag($item, $xmlProduct);
            $this->addBrandTag($item, $xmlProduct);
            $this->addKeywordsTag($item, $xmlProduct);
            $this->addComponentTag($item, $xmlProduct);
            $this->addArticleDataTag($item, $xmlProduct);

            // not category in eng
//            $this->addCategoryTag($item, $xmlProduct);
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

    protected function addNameTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $this->addKeyword($item, $xmlProduct);
        $this->addProperName($item, $xmlProduct);
    }

    protected function addKeyword(SimpleXMLElement $item, array $xmlProduct): void
    {
        $defaultName = $this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT);
        $defaultNameArray = explode(' ', mb_strtolower($defaultName));
        $searchStrings = [
            'bag', 'folder', 'case', 'briefcase', 'wallet', 'handbag', 'backpack', 'holder', 'crossbody',
            'belt', 'organiser', 'luggage', 'protection', 'suitcase', 'cover', 'balm', 'liquid'
        ];
        $defaultSequence = $this->returnNameKeywordSequence($defaultNameArray, $searchStrings);

        $extraName = $this->getName($xmlProduct['description'], self::ADDITIONAL_LANG_SHORT, self::DEFAULT_LANG_SHORT);
        $extraNameArray = explode(' ', mb_strtolower(str_replace(',', '', $extraName)));
        $searchStrings = [
            'kosmetyczka', 'aktówka', 'torba', 'teczka', 'kopertówka', 'portfel', 'plecak', 'listonoszka', 'shopperka',
            'torebka', 'etui', 'biwuar', 'pasek', 'nerka', 'bilonówka', 'wizytownik', 'pokrowiec', 'organizer',
            'walizka', 'protector', 'okładka', 'saszetka', 'balsam', 'płyn', 'pasta'
        ];
        $extraSequence = $this->returnNameKeywordSequence($extraNameArray, $searchStrings);

        $name = $item->addChild('P_NAME_KEYWORD');
        $nameChildFirst = $name->addChild('VALUE', $defaultSequence);
        $nameChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
        $nameChildSecond = $name->addChild('VALUE', $extraSequence);
        $nameChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);
    }

    protected function returnNameKeywordSequence(array $array, array $searchStrings): string
    {
        foreach ($searchStrings as $string) {
            $key = array_search($string, $array);
            if ($key !== false) {
                $foundedWordKey = $key;
            }
        }

        if (!isset($foundedWordKey)) {
            $foundedWordKey = 1;
        }

        $sequence = $array[$foundedWordKey];

        if ($foundedWordKey > 0) {
            $sequence = $array[$foundedWordKey - 1] . ' ' . $array[$foundedWordKey];
        }

        return $sequence;
    }

    protected function addProperName(SimpleXMLElement $item, array $xmlProduct): void
    {
        $defaultName = $this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT);
        $defaultNameArray = explode(' ', $defaultName);
        $defaultWord = mb_strtoupper(end($defaultNameArray));

        $name = $item->addChild('P_NAME_PROPER');
        $nameChildFirst = $name->addChild('VALUE', $defaultWord);
        $nameChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
        $nameChildSecond = $name->addChild('VALUE', $defaultWord);
        $nameChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);
    }

    protected function addArticleDataTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $articleData = $item->addChild('ARTICLEDATA');
        $article = $articleData->addChild('ARTICLE');

        $mediaData = $item->addChild('A_MEDIADATA');
        $this->addImagesTags($mediaData, $xmlProduct['images']['large']['image']);

        $article->addChild('A_NR', $xmlProduct['@attributes']['id']);

        $activeData = $article->addChild('A_ACTIVEDATA');
        $activeDataChild = $activeData->addChild('A_ACTIVE', '1');
        $activeDataChild->addAttribute('channel', 'ch_' . self::CHANNEL_DE_ID);

        $activeDataChild = $activeData->addChild('A_ACTIVE', '1');
        $activeDataChild->addAttribute('channel', 'ch_' . self::CHANNEL_PL_ID);

        $this->addPricesTag($item, $xmlProduct);

        $article->addChild('A_ACTIVE', $this->converterHelper->getStock($xmlProduct['sizes']['size']['stock']) > 0 ? 1 : 0);
        $article->addChild('A_PROD_NR', $xmlProduct['@attributes']['id']);
        $article->addChild('A_STOCK', $this->converterHelper->getStock($xmlProduct['sizes']['size']['stock']));
    }

    protected function addImagesTags(SimpleXMLElement $element, array $images): void
    {
        foreach ($images as $key => $value) {
            if (isset($value['@attributes'])) {
                $media = $element->addChild('A_MEDIA', $value['@attributes']['url']);
                $media->addAttribute('type', 'image');
                $media->addAttribute('sort', $key);
            }
        }
    }

    protected function addPricesTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $priceData = $item->addChild('A_PRICEDATA');

        $price = $priceData->addChild('A_PRICE');
        $price->addAttribute('channel', 'ch_' . self::CHANNEL_DE_ID);
        $price->addAttribute('currency', self::CURRENCY_DE);
        $price->addChild('A_VK', round($xmlProduct['price']['@attributes']['gross'] * self::CURRENCY_DE_VALUE, 2));

        $price = $priceData->addChild('A_PRICE');
        $price->addAttribute('channel', 'ch_' . self::CHANNEL_PL_ID);
        $price->addAttribute('currency', self::CURRENCY_PL);
        $price->addChild('A_VK', round($xmlProduct['price']['@attributes']['gross'], 2));
    }

    protected function addTextTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $text = $item->addChild('P_TEXT');
        $textChildFirst = $text->addChild('VALUE',
            $this->getDescription($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT)
        );
        $textChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
        $textChildSecond = $text->addChild('VALUE',
            $this->getDescription($xmlProduct['description'], self::ADDITIONAL_LANG_SHORT, self::DEFAULT_LANG_SHORT)
        );
        $textChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);
    }

    protected function addBrandTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $brand = $item->addChild('P_BRAND');
        $brand->addAttribute('identifier', 'key');
        $brand->addAttribute('key', $xmlProduct['producer']['@attributes']['id']);
        $brand->addAttribute('name', $xmlProduct['producer']['@attributes']['name']);
    }

    protected function addKeywordsTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $keywords = $item->addChild('P_KEYWORDS');
        $keyword = $keywords->addChild('P_KEYWORD');
        $keywordChildFirst = $keyword->addChild('VALUE',
            $this->getName($xmlProduct['description'], self::DEFAULT_LANG_SHORT, self::ADDITIONAL_LANG_SHORT)
        );
        $keywordChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);
        $keywordChildSecond = $keyword->addChild('VALUE',
            $this->getName($xmlProduct['description'], self::ADDITIONAL_LANG_SHORT, self::DEFAULT_LANG_SHORT)
        );
        $keywordChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);
    }

    protected function addComponentTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $componentData = $item->addChild('P_COMPONENTDATA');
        $component = $componentData->addChild('P_COMPONENT');
        $component->addAttribute('identifier', 'key');
        $component->addAttribute('key', 'material');

        $parameter = [];
        if (isset($xmlProduct['parameters'])) {
            $parameter = $this->converterHelper->getParameterById($xmlProduct['parameters'], self::MATERIAL_ID);
        }

        $parameter = $this->mapMaterialToEng($parameter);
        $componentChildFirst = $component->addChild('VALUE', $parameter[0]);
        $componentChildFirst->addAttribute('xml:lang', self::DEFAULT_LANG);

        $componentChildSecond = $component->addChild('VALUE', $parameter[1]);
        $componentChildSecond->addAttribute('xml:lang', self::ADDITIONAL_LANG);
    }

    protected function mapMaterialToEng(array $parameter): array
    {
        if (empty($parameter)) {
            return ['undefined', 'nie dostępny'];
        }

        switch (reset($parameter)) {
            case 'Skóra ekologiczna':
                return ['eco leather', reset($parameter)];
            case 'Skóra naturalna':
                return ['natural leather', reset($parameter)];
            case 'Pleciony pasek':
                return ['woven belt', reset($parameter)];
            case 'Skóra garbowana roślinnie':
                return ['vegetable tanned leather', reset($parameter)];
            case 'Kodura':
                return ['codura', reset($parameter)];
            default:
                return [reset($parameter), reset($parameter)];
        }
    }

    protected function addCategoryTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $categories = $item->addChild('P_CATEGORIES');
        $category = $categories->addChild('P_CATEGORY', $xmlProduct['category']['@attributes']['name']);
        $category->addAttribute('type', 'cluster');
        $category->addAttribute('identifier', 'key');
        $category->addAttribute('key', 'clust1');
    }
}
