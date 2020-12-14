<?php

namespace App\Service\Converter\Helper;

class Converter
{
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

    public function findLanguage(
        array $data,
        string $mainLanguage,
        string $defaultLanguage
    ): ?int {
        foreach ($data as $key => $value) {
            if (!isset($value['@attributes'])) {
                return null;
            }

            if ($value['@attributes']['xml:lang'] === $mainLanguage) {
                return $key;
            }
        }

        foreach ($data as $key => $value) {
            if ($value['@attributes']['xml:lang'] === $defaultLanguage) {
                return $key;
            }
        }

        return null;
    }

    public function currencyExchange(float $counter, int $value): int
    {
        $exchangedValue = $counter * $value;

        return round($exchangedValue);
    }

    public function getStock(array $stock): int
    {
        if (!isset($stock['@attributes'])) {
            $value = (int)$stock[0]['@attributes']['quantity'];
        }
        if (isset($stock['@attributes'])) {
            $value = (int)$stock['@attributes']['quantity'];
        }

        return $value;
    }
}