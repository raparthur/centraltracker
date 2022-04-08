<?php

namespace App\Repository;

use App\Entity\HeartbeatEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HeartbeatEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method HeartbeatEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method HeartbeatEvent[]    findAll()
 * @method HeartbeatEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HeartbeatEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HeartbeatEvent::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(HeartbeatEvent $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(HeartbeatEvent $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return HeartbeatEvent[] Returns an array of HeartbeatEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HeartbeatEvent
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
