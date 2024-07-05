<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\ProcessBalancerService;
use App\Service\MachineBalancerService;

class MachineController extends AbstractController {
    private $processRepository;
    private $machineRepository;
    private $processBalancerService;
    private $machineBalancerService;

    public function __construct(MachineRepository $machineRepository, ProcessRepository $processRepository, ProcessBalancerService $processBalancerService, MachineBalancerService $machineBalancerService) {
        $this->machineRepository = $machineRepository;
        $this->processRepository = $processRepository;
        $this->processBalancerService = $processBalancerService;
        $this->machineBalancerService = $machineBalancerService;
    }

    /**
     * @Route("/machines", name="add_machine", methods={"POST"})
     * @throws \Exception
     */
    public function addMachine(Request $request): JsonResponse {
        $requestData = json_decode($request->getContent(), true);
        $machine = $this->machineRepository->create($requestData);

        $this->machineBalancerService->rebalance($machine);

        return new JsonResponse(['message' => 'Machine added'], 201);
    }

    /**
     * @Route("/machines/{id}", name="delete_machine", methods={"DELETE"})
     * @throws \Exception
     */
    public function deleteMachine($id) {
        $machine = $this->machineRepository->find($id);

        if (!$machine) {
            return new JsonResponse(['message' => 'Machine not found'], 404);
        }

        $processes = $machine->getProcesses();
        $processIds = array();

        foreach ($processes as $process) {
            $processIds[] = $process->getId();
        }

        foreach ($processes as $process) {
            $process->setMachine(null);
            $this->processRepository->save($process);
        }

        $machine->getProcesses()->clear();
        $this->machineRepository->save($machine);
        $this->machineRepository->delete($machine);;

        foreach ($processIds as $processId) {
            $process = $this->processRepository->find($processId);
            if ($process) {
                $this->processBalancerService->rebalance($process);
            }
        }

        return new JsonResponse(['message' => 'Machine deleted'], 200);
    }

    /**
     * @Route("/machines", name="get_machines", methods={"GET"})
     */
    public function getAllMachinesInfo() : JsonResponse {
        $machines = $this->machineRepository->findAll();

        $data = [];
        foreach ($machines as $machine) {
            $data[$machine->getId()] = [
                'totalMemory' => $machine->getTotalMemory(),
                'totalProcessor' => $machine->getTotalProcessor(),
                'freeMemory' => $machine->getFreeMemory(),
                'freeProcessor' => $machine->getFreeProcessor(),
                'numberOfProcesses' => $machine->getNumberOfProcesses(),
                'processes' => []
            ];

            $machine_processes = $machine->getProcesses();

            if (!empty($machine_processes)) {
                foreach ($machine_processes as $process) {
                    $data[$machine->getId()]['processes'][] = [
                        'id' => $process->getId(),
                        'requiredMemory' => $process->getRequiredMemory(),
                        'requiredProcessor' => $process->getRequiredProcessor()
                    ];
                }
            }
        }

        return new JsonResponse($data);
    }
}