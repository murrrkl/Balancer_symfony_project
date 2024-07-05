<?php

namespace App\Repository;

use App\Entity\Machine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Machine>
 *
 * @method Machine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Machine|null findOneBy(array $criteria, array $orderBy = null)
 * @method Machine[]    findAll()
 * @method Machine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MachineRepository extends ServiceEntityRepository
{
    private $entityManager;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
        $this->entityManager = $registry->getManager();
    }

    /**
     * @throws Exception
     */
    public function create(array $requestData): Machine {
        $machine = new Machine($requestData['total_memory'], $requestData['total_processor']);
        $this->save($machine);

        return $machine;
    }

    /**
     * @throws Exception
     */
    public function save(Machine $machine): void {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($machine);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function delete(Machine $machine): void {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($machine);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function findByIsHighLoad($processLimit) {
        return $this->createQueryBuilder('m')
            ->where('m.numberOfProcesses > :processLimit')
            ->setParameter('processLimit', $processLimit)
            ->getQuery()
            ->getResult();
    }

    public function findByHasSufficientProcesses($processLimit) {
        return $this->createQueryBuilder('m')
            ->where('m.numberOfProcesses = :processLimit')
            ->setParameter('processLimit', $processLimit)
            ->getQuery()
            ->getResult();
    }

    public function findByHasInsufficientProcesses($processLimit) {
        return $this->createQueryBuilder('m')
            ->where('m.numberOfProcesses < :processLimit')
            ->setParameter('processLimit', $processLimit)
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Machine[] Returns an array of Machine objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Machine
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
