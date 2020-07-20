<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    // /**
    //  * @return Item[] Returns an array of Item objects
    //  */
    
    public function findByNullField()
    {
        return $this->createQueryBuilder('i')
            ->Where('i.Price = :val OR i.Image is NULL OR i.Store is NULL or i.Image like :val2')
            ->setParameter('val', 0)
            ->setParameter('val2', "")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByNullFieldByUser($user_id)
    {
        return $this->createQueryBuilder('i')
            ->Where('i.Price = 0 OR i.Image is NULL OR i.Store is NULL')
            ->leftJoin('i.Room', 'room')
            ->andWhere('room.userId = :user')
            ->setParameter('user', $user_id)
            ->getQuery()
            ->getResult()
        ;
    }
    

}
