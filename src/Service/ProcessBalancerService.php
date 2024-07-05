<?php

namespace App\Service;
use App\Entity\Process;

use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProcessBalancerService extends BalancerService {

    function __construct(MachineRepository $machineRepository, ProcessRepository $processRepository,  entityManagerInterface $entityManager){
        parent::__construct($machineRepository, $processRepository, $entityManager);
    }
    public function rebalance(Process $process): void {
        $this->calcAverageLoad();

        $free_machines = $this->machineRepository->findByHasInsufficientProcesses($this->average_load);
        $loaded_machines = $this->machineRepository->findByHasSufficientProcesses($this->average_load);
        $overloaded_machines = $this->machineRepository->findByIsHighLoad($this->average_load);

        if ($process->getRequiredProcessor() > $process->getRequiredMemory()) {
            usort($free_machines, function($a, $b) {
                return $b->getFreeMemory() - $a->getFreeMemory();
            });
            usort($loaded_machines, function($a, $b) {
                return $b->getFreeMemory() - $a->getFreeMemory();
            });
            usort($overloaded_machines, function($a, $b) {
                return $b->getFreeMemory() - $a->getFreeMemory();
            });
        } else {
            usort($free_machines, function($a, $b) {
                return $b->getFreeProcessor() - $a->getFreeProcessor();
            });
            usort($loaded_machines, function($a, $b) {
                return $b->getFreeProcessor() - $a->getFreeProcessor();
            });
            usort($overloaded_machines, function($a, $b) {
                return $b->getFreeProcessor() - $a->getFreeProcessor();
            });
        }

        foreach ($free_machines as $machine) {
            if ($machine->checkMachineProcessCompatibility($process)) {
                $this->bindMachineToProcess($machine, $process);
                return;
            }
        }

        foreach ($loaded_machines as $machine) {
            if ($machine->checkMachineProcessCompatibility($process)) {
                $this->bindMachineToProcess($machine, $process);
                return;
            }
        }

        foreach ($overloaded_machines as $machine) {
            if ($machine->checkMachineProcessCompatibility($process)) {
                $this->bindMachineToProcess($machine, $process);
                return;
            }
        }
    }
}