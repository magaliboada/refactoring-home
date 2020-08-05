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


    public function findByUserField($user_id, $public)
    {
        $query = $this->createQueryBuilder('r')
            ->andWhere('r.userId = :user_id')
            ->setParameter('user_id', $user_id)            
            ->orderBy('r.id', 'ASC');
            // ->setMaxResults(10)
        if ($public) {
            $query->andWhere('r.Public = :public')
            ->setParameter('public', true);
        }

        return $query->getQuery()
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


    public function findByPublic()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.Public = :public')
            ->setParameter('public', true)
            ->orderBy('RAND()')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByType($type)
    {
        $query = $this->createQueryBuilder('r')
        ->andWhere('r.Public = :public')
        ->setParameter('public', true);     

        if (strpos($type, 'Types') == false) {
            $query->andWhere('r.Name = :name')
            ->setParameter('name', $type);
        }

        return $query
        ->orderBy('RAND()')
        ->getQuery()
        ->getResult();
    }

    
}
