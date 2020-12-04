<?php

namespace App\Service\Converter;

use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Provider\ConverterFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use DateTime;

class ConverterWorker
{
    protected ConverterFactory $factory;
    protected string $marketplaceName;
    protected string $projectDir;
    protected const FORMAT = '.xml';

    public function __construct(
        ConverterFactory $factory,
        string $projectDir
    ) {
        $this->factory = $factory;
        $this->projectDir = $projectDir;
    }

    public function convert(string $converterName, UploadedFile $file): void
    {
        $this->marketplaceName = $converterName;
        $converterProvider = $this->factory->getConverterByName($converterName);
        $this->saveFile($converterProvider->convertFile(file_get_contents($file)));
    }

    protected function saveFile(SimpleXMLExtended $simpleXml): void
    {
        $currentTime = new DateTime();
        $filename = $this->projectDir . $this->marketplaceName .
            $currentTime->getTimestamp() . self::FORMAT;

        $simpleXml->saveXML($filename);
    }
}