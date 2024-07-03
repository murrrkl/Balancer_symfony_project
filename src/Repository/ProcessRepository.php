<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Process;

class ProcessRepository extends ServiceEntityRepository {
    private $entityManager;


    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Process::class);
        $this->entityManager = $registry->getManager();
    }

    public function createProcess(array $requestData): Process {
        $machine = new Process();
        $machine->setRequiredMemory($requestData['require_memory'])->setRequiredProcessor(['require_processor']);

        return $machine;
    }

    public function saveProcess(Process $process): void {
        $this->entityManager->persist($process);
        $this->entityManager->flush();
    }

    public function findProcessById($id) {
        return $this->find($id);
    }

    public  function  removeProcess(Process $process) : void {
        $this->entityManager->remove($process);
        $this->entityManager->flush();
    }
}