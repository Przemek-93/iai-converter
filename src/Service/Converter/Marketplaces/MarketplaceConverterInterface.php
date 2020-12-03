<?php

namespace App\Service\Converter\Marketplaces;

use App\Service\Converter\Helper\SimpleXMLExtended;

interface MarketplaceConverterInterface
{
    public function convert(string $file): SimpleXMLExtended;
}