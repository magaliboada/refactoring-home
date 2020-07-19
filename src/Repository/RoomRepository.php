<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    // /**
    //  * @return Room[] Returns an array of Room objects
    //  */
    
    public function findByUserField($user_id)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.userId = :user_id')
            ->setParameter('user_id', $user_id)
            ->orderBy('r.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteByUser($user_id) {

        $qb = $this->createQueryBuilder('r');
        $query = $qb->delete('App:Room', 'r')
        ->Where('r.userId = :user_id')
        ->setParameter('user_id', $user_id)
        ->getQuery();

        $query->execute();
    
    }
    
    

    /*
    publir funrtion findOneBySomeField($value): ?Room
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
