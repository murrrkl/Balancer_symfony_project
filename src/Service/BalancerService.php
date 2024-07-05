<?php

namespace App\Service;

use App\Entity\Machine;
use App\Entity\Process;
use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

class BalancerService {
    protected $machineRepository;
    protected $processRepository;
    protected $entityManager;
    private $machines;
    private $processes;
    protected int $average_load;

    public function __construct(MachineRepository $machineRepository, ProcessRepository $processRepository,  entityManagerInterface $entityManager)
    {
        $this->machineRepository = $machineRepository;
        $this->processRepository = $processRepository;
        $this->entityManager = $entityManager;
    }

    protected function bindMachineToProcess(Machine $machine, Process $process) : void {
        $machine->addProcess($process);

        $this->entityManager->persist($process);
        $this->entityManager->persist($machine);
        $this->entityManager->flush();
    }

    protected function calcAverageLoad() : void {
        $this->machines = $this->machineRepository->findAll();
        $this->processes = $this->processRepository->findAll();

        $this->average_load = floor(count($this->processes) / count($this->machines));
    }
}