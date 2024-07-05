<?php

namespace App\Repository;

use App\Entity\Process;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Process>
 *
 * @method Process|null find($id, $lockMode = null, $lockVersion = null)
 * @method Process|null findOneBy(array $criteria, array $orderBy = null)
 * @method Process[]    findAll()
 * @method Process[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessRepository extends ServiceEntityRepository
{
    private $entityManager;


    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Process::class);
        $this->entityManager = $registry->getManager();
    }

    /**
     * @throws Exception
     */
    public function create(array $requestData): Process {
        $processor = new Process($requestData['required_memory'], $requestData['required_processor']);
        $this->save($processor);

        return $processor;
    }

    /**
     * @throws Exception
     */
    public function save(Process $process): void {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($process);
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
    public function delete(Process $process): void {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($process);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function findByWaitingForMachine() {
        return $this->createQueryBuilder('p')
            ->where('p.machine IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByIdJoinedToMachine(int $processId): ?Process {
        $query = $this->entityManager->createQuery(
            'SELECT p, c
            FROM App\Entity\Process p
            INNER JOIN p.machine c
            WHERE p.id = :id'
        )->setParameter('id', $processId);

        return $query->getOneOrNullResult();
    }

//    /**
//     * @return Process[] Returns an array of Process objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Process
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
