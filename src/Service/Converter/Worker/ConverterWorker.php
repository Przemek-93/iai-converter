<?php

namespace App\Service\Converter\Worker;

use App\Crud\ConvertedFileCrud;
use App\Service\Converter\Helper\SimpleXMLExtended;
use App\Service\Converter\Factory\ConverterFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use DateTime;

class ConverterWorker
{
    protected ConverterFactory $factory;
    protected string $marketplaceName;
    protected string $projectDir;
    protected ConvertedFileCrud $convertedFileCrud;
    protected const FORMAT = '.xml';

    public function __construct(
        ConverterFactory $factory,
        string $projectDir,
        ConvertedFileCrud $convertedFileCrud
    ) {
        $this->factory = $factory;
        $this->projectDir = $projectDir;
        $this->convertedFileCrud = $convertedFileCrud;
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
        $fileName = $this->projectDir . $this->marketplaceName .
            $currentTime->getTimestamp() . self::FORMAT;
        $simpleXml->saveXML($fileName);
        $this->convertedFileCrud->insertConvertedFile($fileName, $this->marketplaceName);
    }

    public function downloadFile(string $url): void
    {
        $name = basename($url);
        file_put_contents($name,file_get_contents($url));
    }
}