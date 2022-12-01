<?php

namespace App\Repository;

use App\Entity\TauxExecPhys;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TauxExecPhys|null find($id, $lockMode = null, $lockVersion = null)
 * @method TauxExecPhys|null findOneBy(array $criteria, array $orderBy = null)
 * @method TauxExecPhys[]    findAll()
 * @method TauxExecPhys[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TauxExecPhysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TauxExecPhys::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(TauxExecPhys $entity, bool $flush = true): void
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
    public function remove(TauxExecPhys $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return TauxExecPhys[] Returns an array of TauxExecPhys objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TauxExecPhys
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
