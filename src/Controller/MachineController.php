<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ProcessBalancerService;

class MachineController extends AbstractController {
    private $machineRepository;
    private $processBalancerService;

    public function __construct(MachineRepository $machineRepository, ProcessBalancerService $processBalancerService)
    {
        $this->machineRepository = $machineRepository;
        $this->processBalancerService = $processBalancerService;
    }

    /**
     * @Route("/machines", name="add_machine", methods={"POST"})
     */
    public function addMachine(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $machine = $this->machineRepository->createMachine($requestData);
        $this->machineRepository->saveMachine($machine);

        // $this->processBalancerService->rebalanceProcesses();

        return new JsonResponse(['message' => 'Machine added'], 201);
    }

    /**
     * @Route("/machines/{id}", name="delete_machine", methods={"DELETE"})
     */
    public function deleteMachine($id) {
        $machine = $this->machineRepository->findMachineById($id);

        if (!$machine) {
            return new JsonResponse(['message' => 'Machine not found'], 404);
        }

        $this->machineRepository->removeMachine($machine);
        // $this->processBalancerService->rebalanceProcesses();
        return new JsonResponse(['message' => 'Machine deleted'], 200);
    }
}