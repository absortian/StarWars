<?php

namespace App\Repository;

use App\Entity\Characters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Characters|null find($id, $lockMode = null, $lockVersion = null)
 * @method Characters|null findOneBy(array $criteria, array $orderBy = null)
 * @method Characters[]    findAll()
 * @method Characters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharactersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Characters::class);
    }
    
    public function findCharacters($search){
        if($search){
            return $this->createQueryBuilder('c')
                ->andWhere('c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%')
                ->getQuery()
                ->getResult(2)
            ;
        }else{
            return $this->createQueryBuilder('c')
                ->getQuery()
                ->getResult(2)
            ;
        }
    }
}
