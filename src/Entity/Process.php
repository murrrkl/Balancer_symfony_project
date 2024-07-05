<?php

namespace App\Entity;

use App\Repository\ProcessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessRepository::class)]
class Process
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $requiredMemory = null;

    #[ORM\Column]
    private ?int $requiredProcessor = null;

    #[ORM\ManyToOne(inversedBy: 'processes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Machine $machine = null;

    public function __construct(int $requiredMemory, int $requiredProcessor) {
        $this->requiredMemory = $requiredMemory;
        $this->requiredProcessor = $requiredProcessor;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequiredMemory(): ?int
    {
        return $this->requiredMemory;
    }

    public function setRequiredMemory(int $requiredMemory): static
    {
        $this->requiredMemory = $requiredMemory;

        return $this;
    }

    public function getRequiredProcessor(): ?int
    {
        return $this->requiredProcessor;
    }

    public function setRequiredProcessor(int $requiredProcessor): static
    {
        $this->requiredProcessor = $requiredProcessor;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }
}
