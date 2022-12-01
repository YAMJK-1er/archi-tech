<?php

namespace App\Entity;

use App\Repository\TauxExecPhysRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TauxExecPhysRepository::class)]
class TauxExecPhys
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $delai;

    #[ORM\Column(type: 'integer', nullable: true , options:['default'=>0])]
    private $duree;

    #[ORM\Column(type: 'float', nullable: true , options:['default'=>0])]
    private $taux;

    #[ORM\OneToOne(inversedBy: 'tauxExecPhys', targetEntity: Projet::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDelai(): ?int
    {
        return $this->delai;
    }

    public function setDelai(int $delai): self
    {
        $this->delai = $delai;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

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
