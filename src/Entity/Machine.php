<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $totalMemory;

    #[ORM\Column(type: 'integer')]
    private $totalProcessor;

    #[ORM\Column(type: 'integer')]
    private $numberOfProcesses = 0;

    /**
     * @ORM\OneToMany(targetEntity=Process::class, mappedBy="machine", orphanRemoval=true)
     */
    private $processes;

    public function __construct() {
        $this->processes = new ArrayCollection();
    }

    /**
     * @return Collection|Process[]
     */
    public function getProcesses() {
        return $this->processes;
    }

    public function addProcess(Process $process) {
        if (!$this->processes->contains($process)) {
            $this->processes[] = $process;
            $process->setMachine($this);
            $this->numberOfProcesses++;
        }
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function setTotalMemory($totalMemory) : static {
        $this->totalMemory = $totalMemory;

        return $this;
    }

    public function setTotalProcessor($totalProcessor) : static {
        $this->totalProcessor = $totalProcessor;

        return $this;
    }

    public function setNumberOfProcesses(int $numberOfProcesses) : static {
        $this->numberOfProcesses = $numberOfProcesses;

        return $this;
    }

    public function getTotalMemory() {
        return $this->totalMemory;

    }

    public function getTotalProcessor() {
        return $this->totalProcessor;
    }

    public function getNumberOfProcesses() {
        return $this->numberOfProcesses;
    }

    public function removeProcess(Process $process) : static {
        if ($this->processes->contains($process)) {
            $this->processes->removeElement($process);
            if ($process->getMachine() === $this) {
                $process->setMachine(null);
            }
        }
        return $this;
    }
}