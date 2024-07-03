<?php

namespace App\Entity;

use App\Repository\ProcessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessRepository::class)]
class Process
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $requiredMemory;

    #[ORM\Column(type: 'integer')]
    private $requiredProcessor;

    /**
     * @ORM\ManyToOne(targetEntity=Machine::class, inversedBy="process")
     * @ORM\JoinColumn(nullable=true)
     */
    private $machine = null;

    public function getId() {
        return $this->id;
    }

    public function setRequiredMemory($requiredMemory) : static {
        $this->requiredMemory = $requiredMemory;

        return $this;
    }

    public function setRequiredProcessor($requiredProcessor) : static{
        $this->requiredProcessor = $requiredProcessor;

        return $this;
    }

    public function setMachine(Machine $machine) : static {
        $this->machine = $machine;

        return $this;
    }

    public function getRequiredMemory() {
        return $this->requiredMemory;
    }

    public function getRequiredProcessor() {
        return $this->requiredProcessor;
    }

    public function getMachine() {
        return $this->machine;
    }
}
