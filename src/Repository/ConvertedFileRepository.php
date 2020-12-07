<?php

namespace App\Repository;

use App\Entity\ConvertedFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConvertedFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConvertedFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConvertedFile[]    findAll()
 * @method ConvertedFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConvertedFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConvertedFile::class);
    }

    public function getAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult();
    }
}
