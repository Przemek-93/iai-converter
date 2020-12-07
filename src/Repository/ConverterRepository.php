<?php

namespace App\Repository;

use App\Entity\Converter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Converter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Converter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Converter[]    findAll()
 * @method Converter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConverterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Converter::class);
    }
}
