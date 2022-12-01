<?php

namespace App\Repository;

use App\Entity\TauxExecFin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TauxExecFin|null find($id, $lockMode = null, $lockVersion = null)
 * @method TauxExecFin|null findOneBy(array $criteria, array $orderBy = null)
 * @method TauxExecFin[]    findAll()
 * @method TauxExecFin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TauxExecFinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TauxExecFin::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(TauxExecFin $entity, bool $flush = true): void
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
    public function remove(TauxExecFin $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return TauxExecFin[] Returns an array of TauxExecFin objects
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
    public function findOneBySomeField($value): ?TauxExecFin
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
