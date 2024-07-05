<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Route;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $totalMemory = null;

    #[ORM\Column]
    private ?int $totalProcessor = null;

    #[ORM\Column]
    private ?int $freeMemory = null;

    #[ORM\Column]
    private ?int $freeProcessor = null;

    #[ORM\Column]
    private ?int $numberOfProcesses = null;

    #[ORM\OneToMany(mappedBy: 'machine', targetEntity: Process::class)]
    private Collection $processes;

    public function __construct(int $totalMemory, int $totalProcessor) {
        $this->totalMemory = $totalMemory;
        $this->totalProcessor = $totalProcessor;

        $this->freeMemory = $totalMemory;
        $this->freeProcessor = $totalProcessor;
        $this->numberOfProcesses = 0;

        $this->processes = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getTotalMemory(): ?int {
        return $this->totalMemory;
    }

    public function setTotalMemory(int $totalMemory): static {
        $this->totalMemory = $totalMemory;

        return $this;
    }

    public function getTotalProcessor(): ?int {
        return $this->totalProcessor;
    }

    public function setTotalProcessor(int $totalProcessor): static {
        $this->totalProcessor = $totalProcessor;

        return $this;
    }

    public function getFreeMemory(): ?int {
        return $this->freeMemory;
    }

    public function setFreeMemory(int $freeMemory): static {
        $this->freeMemory = $freeMemory;

        return $this;
    }

    public function getFreeProcessor(): ?int {
        return $this->freeProcessor;
    }

    public function setFreeProcessor(int $freeProcessor): static {
        $this->freeProcessor = $freeProcessor;

        return $this;
    }

    public function getNumberOfProcesses(): ?int {
        return $this->numberOfProcesses;
    }

    public function setNumberOfProcesses(int $numberOfProcesses): static {
        $this->numberOfProcesses = $numberOfProcesses;

        return $this;
    }

    /**
     * @return Collection<int, Process>
     */
    public function getProcesses(): Collection {
        return $this->processes;
    }

    public function addProcess(Process $process): static {
        if (!$this->processes->contains($process)) {
            $this->processes->add($process);
            $process->setMachine($this);
            $this->freeMemory -= $process->getRequiredMemory();
            $this->freeProcessor -= $process->getRequiredProcessor();
            $this->numberOfProcesses++;
        }

        return $this;
    }

    public function removeProcess(Process $process): static {
        if ($this->processes->removeElement($process)) {

            if ($process->getMachine() === $this) {
                $process->setMachine(null);
            }
        }

        return $this;
    }

    public function checkMachineProcessCompatibility(Process $process) : bool {
        if ($this->getFreeProcessor() >= $process->getRequiredProcessor() && $this->getFreeMemory() >= $process->getRequiredMemory()) {
            return true;
        } else {
            return false;
        }
    }

    public function freeingUpResources(Process $process) {
        $this->freeMemory += $process->getRequiredMemory();
        $this->freeProcessor += $process->getRequiredProcessor();
        $this->numberOfProcesses--;
    }
}
