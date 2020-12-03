<?php

namespace App\Service\Converter;

use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Provider\ConverterFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConverterWorker
{
    protected ConverterFactory $factory;

    public function __construct(
        ConverterFactory $factory
    ) {
        $this->factory = $factory;
    }

    public function convert(string $converterName, UploadedFile $file): void
    {
        $converterProvider = $this->factory->getConverterByName($converterName);
        $a = file_get_contents($file);
        $this->saveFile($converterProvider->convertFile(file_get_contents($file)));
    }

    protected function saveFile(SimpleXMLExtended $simpleXml): void
    {
        $simpleXml->saveXML($this->createFileName($simpleXml));
    }

    protected function createFileName($simpleXml)
    {
        return 'dupa';
    }
}