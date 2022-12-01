<?php

namespace App\Entity;

use App\Repository\TauxExecFinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TauxExecFinRepository::class)]
class TauxExecFin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'bigint')]
    private $budget;

    #[ORM\Column(type: 'bigint', nullable: true , options:['default'=>0])]
    private $depenses;

    #[ORM\Column(type: 'float', nullable: true , options:['default'=>0])]
    private $taux;

    #[ORM\OneToOne(inversedBy: 'tauxExecFin', targetEntity: Projet::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getDepenses(): ?string
    {
        return $this->depenses;
    }

    public function setDepenses(?string $depenses): self
    {
        $this->depenses = $depenses;

        return $this;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(?float $taux): self
    {
        $this->taux = $taux;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }
}
