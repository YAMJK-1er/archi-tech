<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $intitule;

    #[ORM\Column(type: 'integer')]
    private $delai;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $debut_reel;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_fin;

    #[ORM\Column(type: 'boolean', nullable: true , options:['default' => false])]
    private $est_realise;

    #[ORM\ManyToOne(targetEntity: Planning::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(nullable: false)]
    private $planning;

    #[ORM\Column(type: 'integer' , nullable:true)]
    private $delai_reel;

    public function __construct()
    {
        $this->debut_prev = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
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

    public function getDebutReel(): ?\DateTimeInterface
    {
        return $this->debut_reel;
    }

    public function setDebutReel(?\DateTimeInterface $debut_reel): self
    {
        $this->debut_reel = $debut_reel;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getEstRealise(): ?bool
    {
        return $this->est_realise;
    }

    public function setEstRealise(?bool $est_realise): self
    {
        $this->est_realise = $est_realise;

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): self
    {
        $this->planning = $planning;

        return $this;
    }

    public function getDelaiReel(): ?int
    {
        return $this->delai_reel;
    }

    public function setDelaiReel(int $delai_reel): self
    {
        $this->delai_reel = $delai_reel;

        return $this;
    }
}
