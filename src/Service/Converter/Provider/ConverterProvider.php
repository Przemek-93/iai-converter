<?php

namespace App\Service\Converter\Provider;

use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Marketplaces\MarketplaceConverterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConverterProvider
{
    protected MarketplaceConverterInterface $converter;

    public function __construct(
        MarketplaceConverterInterface $converter
    ) {
        $this->converter = $converter;
    }

    public function convertFile(string $file): SimpleXMLExtended
    {
        return $this->converter->convert($file);
    }
}