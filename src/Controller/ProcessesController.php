<?php

namespace App\Controller;

use App\Repository\ProcessRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ProcessBalancerService;

class ProcessesController extends AbstractController {
    private $processRepository;
    private $processBalancerService;

    public function __construct(ProcessRepository $processRepository, ProcessBalancerService $processBalancerService)
    {
        $this->processRepository = $processRepository;
        $this->processBalancerService = $processBalancerService;
    }

    /**
     * @Route("/processes", name="add_process", methods={"POST"})
     */
    public function addProcess(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);

        $process = $this->processRepository->createProcess($requestData);
        $this->processRepository->saveProcess($process);

        // $this->processBalancerService->rebalanceProcesses();
        return new JsonResponse(['message' => 'Process added'], 201);
    }

    /**
     * @Route("/processes/{id}", name="delete_process", methods={"DELETE"})
     */
    public function deleteProcess($id)
    {
        $process = $this->processRepository->findProcessById($id);

        if (!$process) {
            return new JsonResponse(['message' => 'Process not found'], 404);
        }

        $this->processRepository->removeProcess($process);
        // $this->processBalancerService->rebalanceProcesses();
        return new JsonResponse(['message' => 'Process deleted'], 200);
    }
}