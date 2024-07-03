<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Machine;

class MachineRepository extends ServiceEntityRepository {
    private $entityManager;

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Machine::class);
        $this->entityManager = $registry->getManager();
    }

    public function createMachine(array $requestData): Machine {
        $machine = new Machine();
        $machine->setTotalMemory($requestData['total_memory'])->setTotalProcessor(['total_processor'])->setNumberOfProcesses(0);

        return $machine;
    }

    public function saveMachine(Machine $machine): void {
        $this->entityManager->persist($machine);
        $this->entityManager->flush();
    }

    public function findMachineById($id) {
        return $this->find($id);
    }

    public  function  removeMachine(Machine $machine) : void {
        $this->entityManager->remove($machine);
        $this->entityManager->flush();
    }
}