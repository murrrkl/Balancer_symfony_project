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

class ProcessesController extends AbstractController {
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
     * @Route("/processes", name="add_process", methods={"POST"})
     */
    public function addProcess(Request $request) {
        $requestData = json_decode($request->getContent(), true);
        $process = $this->processRepository->create($requestData);

        $this->processBalancerService->rebalance($process);

        return new JsonResponse(['message' => 'Process added'], 201);
    }

    /**
     * @Route("/processes/{id}", name="delete_process", methods={"DELETE"})
     * @throws \Exception
     */
    public function deleteProcess($id) {
        $process = $this->processRepository->find($id);

        if (!$process) {
            return new JsonResponse(['message' => 'Process not found'], 404);
        }

        $machine = $process->getMachine();

        if ($machine !== null) {
            $process = $this->processRepository->findOneByIdJoinedToMachine($id);
            $machine = $process->getMachine();
            $machine->freeingUpResources($process);

            $this->machineRepository->save($machine);
            $machine->removeProcess($process);

            $this->machineBalancerService->rebalance($machine);
        }

        $this->processRepository->delete($process);

        return new JsonResponse(['message' => 'Process deleted'], 200);
    }

    /**
     * @Route("/processes", name="get_processes", methods={"GET"})
     */
    public function getAllProcessesInfo() : JsonResponse {
        $processes = $this->processRepository->findAll();

        $data = [];
        foreach ($processes as $process) {
            $machineId = $process->getMachine()?->getId();

            $data[$process->getId()] = [
                'requiredMemory' => $process->getRequiredMemory(),
                'requiredProcessor' => $process->getRequiredProcessor(),
                'machine_num' => $machineId,
            ];
        }
        return new JsonResponse($data);
    }

}