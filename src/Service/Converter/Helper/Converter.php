<?php

namespace App\Service\Converter\Helper;

abstract class Converter
{
    protected const LANG = 'pol';

    protected function getQty($stocks): int
    {
        if (isset($stocks['@attributes'])) {
            return $stocks['@attributes']['quantity'];
        }

        $qty = 0;
        foreach ($stocks as $stock) {
            $qty += (int)$stock['@attributes']['quantity'];
        }
        return $qty;
    }

    protected function hasMultipleSizes($xmlProduct): bool
    {
        if (!isset($xmlProduct['sizes']['size']['@attributes'])) {
            return true;
        }
        return false;
    }

    protected function getWarrantyInMonths($warranty)
    {
        if(strpos($warranty, 'lat')){
            preg_match_all('!\d+!', $warranty, $years);
            return $years[0][0]*12;
        }

        preg_match_all('!\d+!', $warranty, $months);
        return $months[0][0];
    }

    protected function validateSimple($xmlProduct): bool
    {
        if (isset($xmlProduct['@attributes']['id'], $xmlProduct['category']['@attributes']['name'],
            $xmlProduct['price']['@attributes']['net'], $xmlProduct['card']['@attributes']['url'], $xmlProduct['images']['large']['image'],
            $xmlProduct['description']['name'][$this->findLanguage($xmlProduct['description']['name'], self::LANG)]['@cdata'],
            $xmlProduct['producer']['@attributes']['name'], $xmlProduct['sizes']['size']['@attributes']['code'])) {
            return true;
        }
        return false;
    }

    protected function validateConfigurable($xmlProduct, $size): bool
    {
        if (isset($xmlProduct['@attributes']['id'], $xmlProduct['category']['@attributes']['name'],
            $xmlProduct['price']['@attributes']['net'], $xmlProduct['card']['@attributes']['url'], $xmlProduct['images']['large']['image'],
            $xmlProduct['description']['name'][$this->findLanguage($xmlProduct['description']['name'], self::LANG)]['@cdata'],
            $xmlProduct['producer']['@attributes']['name'], $size['@attributes']['code'])) {
            return true;
        }
        return false;
    }

    protected function getProductByAttribute($xmlProduct, $size): bool
    {
        if (isset($xmlProduct['@attributes']['id'], $xmlProduct['category']['@attributes']['name'],
            $xmlProduct['price']['@attributes']['net'], $xmlProduct['card']['@attributes']['url'], $xmlProduct['images']['large']['image'],
            $xmlProduct['description']['name'][$this->findLanguage($xmlProduct['description']['name'], self::LANG)]['@cdata'],
            $xmlProduct['producer']['@attributes']['name'], $size['@attributes']['code'])) {
            return true;
        }
        return false;
    }

    protected function findLanguage($data, $language)
    {
        foreach ($data as $key => $value) {
            if ($value['@attributes']['xml:lang'] === $language) {
                return $key;
            }
        }
    }
}