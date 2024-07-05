<?php

namespace App\Service;

use App\Entity\Machine;
use App\Entity\Process;

use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class MachineBalancerService extends BalancerService {
    function __construct(MachineRepository $machineRepository, ProcessRepository $processRepository,  entityManagerInterface $entityManager){
        parent::__construct($machineRepository, $processRepository, $entityManager);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function rebalance(Machine $machine) : void {
        // Получаем все процессы, у которых поле machine === null (в ожидании)
        $waitingProcesses = $this->processRepository->findByWaitingForMachine();
        $this->calcAverageLoad();

        foreach ($waitingProcesses as $process) {
            if ($machine->checkMachineProcessCompatibility($process)) {
                $this->bindMachineToProcess($machine, $process);
            }
        }

        if ($machine->getNumberOfProcesses() >= $this->average_load) {
            return;
        }

        // Получаем все машины, которые привысили лимит нагрузки
        $cur_machine_mass = $this->machineRepository->findByIsHighLoad($this->average_load);

        foreach ($cur_machine_mass as $cur_machine) {

            $cur_processes = $cur_machine->getProcesses()->toArray();

            if ($machine->getTotalMemory() > $machine->getTotalProcessor()) {
                if ($machine->getTotalMemory() > $cur_machine->getTotalMemory()) {
                    usort($cur_processes, function($a, $b) {
                        return $b->getRequiredMemory() - $a->getRequiredMemory();
                    });
                } else {
                    usort($cur_processes, function($a, $b) {
                        return $a->getRequiredMemory() - $b->getRequiredMemory();
                    });
                }
            } else {
                if ($machine->getTotalProcessor() > $cur_machine->getTotalMemory()) {
                    usort($cur_processes, function($a, $b) {
                        return $b->getRequiredProcessor() - $a-> getRequiredProcessor();
                    });
                } else {
                    usort($cur_processes, function($a, $b) {
                        return $a->getRequiredProcessor() - $b-> getRequiredProcessor();
                    });
                }
            }

            foreach ($cur_processes as $cur_process) {
                if ($cur_machine->getNumberOfProcesses() <= $this->average_load) {
                    continue;
                }

                if ($cur_machine->getNumberOfProcesses() > $this->average_load && $machine->checkMachineProcessCompatibility($cur_process)) {

                    $this->breakConnection($cur_process);
                    $this->bindMachineToProcess($machine, $cur_process);

                    if ($machine->getNumberOfProcesses() == $this->average_load) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    private function breakConnection(Process $process) : void {
        $process = $this->processRepository->findOneByIdJoinedToMachine($process->getId());
        $machine = $process->getMachine();
        $machine->freeingUpResources($process);
        $this->machineRepository->save($machine);

        $machine->removeProcess($process);
        $this->processRepository->save($process);
    }
}