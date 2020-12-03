<?php

namespace App\Crud;

use App\Entity\Converter;
use App\Repository\ConverterRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConverterCrud extends AbstractDoctrineCrud
{
    protected string $entityName = Converter::class;
    protected EntityManagerInterface $entityManager;
    protected SerializerInterface $serializer;
    protected ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($entityManager, $validator);
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function createConverter(Request $request, $groups = 'json'): string
   {
        $converter = new Converter();
        $converter->setName($request->get('name'));
        $this->entityManager->persist($converter);
        $this->entityManager->flush();

        return $this->serializer->serialize($converter, $groups);
   }

   public function getConverters(): array
   {
       return $this->repository->findAll();
   }
}
