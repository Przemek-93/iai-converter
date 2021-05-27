<?php

namespace App\Service\Converter\Marketplaces\Zalando;

use App\Service\Converter\Helper\Converter;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Helper\XmlToArray;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;
use SimpleXMLElement;

class ZalandoConverter implements MarketplaceConverterInterface
{
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
        $simpleXMLMain = new SimpleXMLExtended(ZalandoHelper::HEADER);
        $simpleXML = $simpleXMLMain->addChild('PRODUCTDATA');
        $simpleXML->addAttribute('type', 'full');
        $file = XmlToArray::createArray($file);
        $nameArray = [];
        foreach ($file['offer']['products']['product'] as $xmlProduct) {
            if (!$this->zalandoHelper->excludeFailureProducts($nameArray, $xmlProduct)) {
                continue;
            }

            $item = $simpleXML->addChild('PRODUCT');
            $item->addChild('P_NR', $xmlProduct['@attributes']['id']);
            $this->addNamesChild($item, $xmlProduct);
            $this->addTextTag($item, $xmlProduct);
            $this->addBrandTag($item, $xmlProduct);
            $this->addKeywordsTag($item, $xmlProduct);
            $this->addComponentTag($item, $xmlProduct);
            $this->addTagsTag($item, $xmlProduct);
            $this->addCategoryTag($item, $xmlProduct);
            $this->addArticleDataTag($item, $xmlProduct);
            // not category in eng
        }
        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $simpleXMLMain;
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
        $name = $item->addChild('P_NAME');
        $nameChildFirst = $name->addChild('VALUE', $this->zalandoHelper->produceSequence($xmlProduct));
        $nameChildFirst->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG, 'xml');
    }

    protected function addNamesChild(SimpleXMLElement $item, array $xmlProduct): void
    {
        $words = explode(' - ', $this->zalandoHelper->produceSequence($xmlProduct));
        $pname = $item->addChild('P_NAME_KEYWORD');
        $value = $pname->addChild('VALUE', ucfirst($words[0]));
        $value->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');

        if (!isset($words[1])) {
            return;
        }

        if (in_array(strtolower($words[1]), ['dark', 'black', 'cognac', 'men\'s', 'women\'s', 'felice', 'passport', 'brown', 'men', 'women'])) {
            $words[1] = array_rand(array_flip(['functional', 'Leather', 'Capacious', 'elegant', 'Modern', 'classic', 'Fashionable', 'Smart', 'Stylish', 'compact', 'Practical', 'Solid']));
        }

        $pname = $item->addChild('P_NAME_PROPER');
        $value = $pname->addChild('VALUE', ucfirst($words[1]));
        $value->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');
    }

    protected function addProperName(SimpleXMLElement $item, array $xmlProduct): void
    {
        $defaultName = $this->zalandoHelper->getName(
            $xmlProduct['description'],
            ZalandoHelper::DEFAULT_LANG_SHORT,
            ZalandoHelper::ADDITIONAL_LANG_SHORT
        );
        $defaultNameArray = explode(' ', $defaultName);
        $defaultWord = mb_strtoupper(end($defaultNameArray));

        $name = $item->addChild('P_NAME_PROPER');
        $nameChildFirst = $name->addChild('VALUE', $defaultWord);
        $nameChildFirst->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');
    }

    protected function addArticleDataTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $articleData = $item->addChild('ARTICLEDATA');
        $article = $articleData->addChild('ARTICLE');

        $article->addChild('A_NR', $xmlProduct['@attributes']['id']);

        $activeData = $article->addChild('A_ACTIVEDATA');
        $activeDataChild = $activeData->addChild('A_ACTIVE', '1');
        $activeDataChild->addAttribute('channel', ZalandoHelper::CHANNEL_DE_ID);

        $activeDataChild = $activeData->addChild('A_ACTIVE', '1');
        $activeDataChild->addAttribute('channel', ZalandoHelper::CHANNEL_PL_ID);

        $article->addChild('A_ACTIVE', '1');

        $this->addEan($article, $xmlProduct);

        $article->addChild('A_PROD_NR', $xmlProduct['@attributes']['id']);

        $variantData = $article->addChild('A_VARIANTDATA');
        $this->addVariant($variantData, $xmlProduct);

        $this->addPricesTag($article, $xmlProduct);

        $mediaData = $article->addChild('A_MEDIADATA');
        $this->addImagesTags($mediaData, $xmlProduct['images']['large']['image']);
    }

    protected function addEan(SimpleXMLElement $element, array $xmlProduct): void
    {
        if (isset($xmlProduct['sizes']['size']['@attributes']['code_producer'])) {
            $element->addChild('A_EAN', $xmlProduct['sizes']['size']['@attributes']['code_producer']);
        }
    }

    protected function addVariant(SimpleXMLElement $element, array $xmlProduct): void
    {
        $variant = $element->addChild('A_VARIANT');
        $variant->addAttribute('identifier', 'key');
        $variant->addAttribute('key', 'size');

        $value = $variant->addChild('VALUE', 'One Size');
        $value->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');

        if (!isset($xmlProduct['group']['group_by_parameter']['product_value']['name'][1]['@value'])) {
            return;
        }

        $variant = $element->addChild('A_VARIANT');
        $variant->addAttribute('identifier', 'key');
        $variant->addAttribute('key', 'color');

        $value = $variant->addChild('VALUE', ucfirst($xmlProduct['group']['group_by_parameter']['product_value']['name'][1]['@value']));
        $value->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');
    }

    protected function addImagesTags(SimpleXMLElement $element, array $images): void
    {
        foreach ($images as $key => $value) {
            if (isset($value['@attributes'])) {
                if (in_array($value['@attributes']['url'], ZalandoHelper::FAILURE_IMAGES)) {
                    continue;
                }
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
        $price->addAttribute('channel', ZalandoHelper::CHANNEL_DE_ID);
        $price->addAttribute('currency', ZalandoHelper::CURRENCY_DE);
        $price->addChild(
            'A_VK',
            round($xmlProduct['price']['@attributes']['gross'] / ZalandoHelper::CURRENCY_DE_VALUE, 2)
        );

        $price = $priceData->addChild('A_PRICE');
        $price->addAttribute('channel', ZalandoHelper::CHANNEL_PL_ID);
        $price->addAttribute('currency', ZalandoHelper::CURRENCY_PL);
        $price->addChild('A_VK', round($xmlProduct['price']['@attributes']['gross'], 2));
    }

    protected function addTextTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $text = $item->addChild('P_TEXT');
        $textChildFirst = $text->addChild('VALUE',
            $this->getDescription(
                $xmlProduct['description'],
                ZalandoHelper::DEFAULT_LANG_SHORT,
                ZalandoHelper::ADDITIONAL_LANG_SHORT
            )
        );
        $textChildFirst->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');
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
        $sequence = str_replace(' - ', ' ', $this->zalandoHelper->produceSequence($xmlProduct));
        $arraySequences = explode(' ', $sequence);
        $keywords = $item->addChild('P_KEYWORDS');
        foreach ($arraySequences as $word) {
            $keyword = $keywords->addChild('P_KEYWORD');
            $keywordChildFirst = $keyword->addChild('VALUE', $word);
            $keywordChildFirst->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');
        }
    }

    protected function addComponentTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $componentData = $item->addChild('P_COMPONENTDATA');
        $component = $componentData->addChild('P_COMPONENT');
        $component->addAttribute('identifier', 'key');
        $component->addAttribute('key', 'material');

        $parameter = [];
        if (isset($xmlProduct['parameters'])) {
            $parameter = $this->converterHelper->getParameterById(
                $xmlProduct['parameters'],
                ZalandoHelper::MATERIAL_ID
            );
        }

        $parameter = $this->mapMaterialToEng($parameter);
        $componentChildFirst = $component->addChild('VALUE', '100% ' . $parameter[0]);
        $componentChildFirst->addAttribute('xml:lang', ZalandoHelper::DEFAULT_LANG,'xml');
    }

    protected function mapMaterialToEng(array $parameter): array
    {
        if (empty($parameter)) {
            return ['eco', 'nie dostępny'];
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

    protected function addTagsTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $tagsData = $item->addChild('P_TAGS');

        $tag = $tagsData->addChild('P_TAG');
        $tag->addAttribute('identifier', 'key');
        $tag->addAttribute('key', 'gender');
        $tagsChild = $tag->addChild('VALUES');
        $tagsChild = $tagsChild->addChild('VALUE');
        $tagsChild->addAttribute('identifier', 'key');
        $tagsChild->addAttribute(
            'key',
            $this->getGenderByProductCode(mb_strtoupper((string)$xmlProduct['@attributes']['code_on_card']))
        );

        $tag = $tagsData->addChild('P_TAG');
        $tag->addAttribute('identifier', 'key');
        $tag->addAttribute('key', 'age group');
        $tagsChild = $tag->addChild('VALUES');
        $tagsChild = $tagsChild->addChild('VALUE');
        $tagsChild->addAttribute('identifier', 'key');
        $tagsChild->addAttribute('key', ZalandoHelper::AGE_GROUP);

        $tag = $tagsData->addChild('P_TAG');
        $tag->addAttribute('identifier', 'key');
        $tag->addAttribute('key', 'size grid');
        $tagsChild = $tag->addChild('VALUES');
        $tagsChild = $tagsChild->addChild('VALUE');
        $tagsChild->addAttribute('identifier', 'key');
        $tagsChild->addAttribute('key', ZalandoHelper::SIZE_GRID);

        $tag = $tagsData->addChild('P_TAG');
        $tag->addAttribute('identifier', 'key');
        $tag->addAttribute('key', 'season');
        $tagsChild = $tag->addChild('VALUES');
        $tagsChild = $tagsChild->addChild('VALUE');
        $tagsChild->addAttribute('identifier', 'key');
        $tagsChild->addAttribute('key', ZalandoHelper::SEASON);
    }

    protected function getGenderByProductCode(string $codeOnCard): string
    {
        foreach (ZalandoHelper::MALE_CODES as $code) {
            if (strpos($codeOnCard, $code)) {
                return 'male';
            }
        }

        foreach (ZalandoHelper::FEMALE_CODES as $code) {
            if (strpos($codeOnCard, $code)) {
                return 'female';
            }
        }

        foreach (ZalandoHelper::UNISEX_CODES as $code) {
            if (strpos($codeOnCard, $code)) {
                return 'unisex';
            }
        }

        return 'unisex';
    }

    protected function addCategoryTag(SimpleXMLElement $item, array $xmlProduct): void
    {
        $categories = $item->addChild('P_CATEGORIES');
        $category = $categories->addChild(
            'P_CATEGORY',
            mb_strtoupper((str_replace('/', ' | ', $xmlProduct['category']['@attributes']['name'])))
        );
        $category->addAttribute('type', 'cluster');
        $category->addAttribute('identifier', 'key');
        $category->addAttribute('key', 'clust1');
    }
}
