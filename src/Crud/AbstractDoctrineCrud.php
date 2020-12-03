<?php

namespace App\Crud;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;

abstract class AbstractDoctrineCrud
{
    protected EntityManagerInterface $entityManager;
    protected ValidatorInterface $validator;
    protected ServiceEntityRepository $repository;
    protected string $entityName;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        if (empty($this->entityName)) {
            throw new \LogicException('Variable $entityName has to be specified');
        }
        $this->repository = $entityManager->getRepository($this->entityName);
        if (!$this->repository instanceof ServiceEntityRepository) {
            throw new \LogicException('Repository for class '.$this->entityName.' has not been found.');
        }
    }

    protected function convertErrors($errors): void
    {
        if (count($errors)) {
            $exceptionContext = [];
            foreach ($errors as $error) {
                $exceptionContext[] = sprintf(
                    'Attribute: [%s]. Error: %s',
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
            // 400 Bad request
            throw new Exception('Validation failed', 400, $exceptionContext);
        }
    }
}