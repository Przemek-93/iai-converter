<?php

namespace App\Crud;

use App\Entity\ConvertedFile;
use App\Repository\ConvertedFileRepository;
use App\Repository\ConverterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConvertedFileCrud extends AbstractDoctrineCrud
{
    protected string $entityName = ConvertedFile::class;
    protected ConverterRepository $converterRepo;
    protected ConvertedFileRepository $convertedFileRepo;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ConverterRepository $converterRepo,
        ConvertedFileRepository $convertedFileRepo
    )
    {
        $this->converterRepo = $converterRepo;
        $this->convertedFileRepo = $convertedFileRepo;
        parent::__construct($entityManager, $validator);
    }

    public function insertConvertedFile(string $name, string $converterName): void
    {
        $convertedFile = new ConvertedFile();
        $convertedFile->setName($name);
        $convertedFile->setConverter($this->converterRepo->findOneBy(['name' => $converterName]));
        $this->entityManager->persist($convertedFile);
        $this->entityManager->flush();
    }

    public function getAllFiles(): array
    {
        return $this->convertedFileRepo->getAll();
    }

    public function deleteFile(string $name): void
    {
        $entity = $this->convertedFileRepo->findOneBy(['name' => $name]);
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $fileSystem = new Filesystem();
        $fileSystem->remove($name);
    }
}
