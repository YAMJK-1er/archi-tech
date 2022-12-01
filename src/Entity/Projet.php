<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'datetime')]
    private $demarrage;

    #[ORM\Column(type: 'integer')]
    private $delai;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $fin;

    #[ORM\Column(type: 'bigint')]
    private $budget;

    #[ORM\Column(type: 'boolean', nullable: true , options:["default"=>false])]
    private $est_termine;

    #[ORM\OneToOne(mappedBy: 'projet', targetEntity: Planning::class, cascade: ['persist', 'remove'])]
    private $planning;

    #[ORM\OneToOne(mappedBy: 'projet', targetEntity: Approvisionnement::class, cascade: ['persist', 'remove'])]
    private $approvisionnement;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'projets')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\OneToOne(mappedBy: 'projet', targetEntity: TauxExecFin::class, cascade: ['persist', 'remove'])]
    private $tauxExecFin;

    #[ORM\OneToOne(mappedBy: 'projet', targetEntity: TauxExecPhys::class, cascade: ['persist', 'remove'])]
    private $tauxExecPhys;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Observation::class)]
    private $observations;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Images::class)]
    private $images;

    #[ORM\Column(type: 'string', length: 255)]
    private $code;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Depense::class)]
    private $depenses;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Plan::class)]
    private $plans;

    public function __construct()
    {
        $this->observations = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->demarrage = new DateTimeImmutable();
        $this->plans = new ArrayCollection();
        $this->demarrage = new DateTimeImmutable();       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDemarrage(): ?\DateTimeInterface
    {
        return $this->demarrage;
    }

    public function setDemarrage(\DateTimeInterface $demarrage): self
    {
        $this->demarrage = $demarrage;

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

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(?\DateTimeInterface $fin): self
    {
        $this->fin = $fin;

        return $this;
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

    public function getEstTermine(): ?bool
    {
        return $this->est_termine;
    }

    public function setEstTermine(?bool $est_termine): self
    {
        $this->est_termine = $est_termine;

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(Planning $planning): self
    {
        // set the owning side of the relation if necessary
        if ($planning->getProjet() !== $this) {
            $planning->setProjet($this);
        }

        $this->planning = $planning;

        return $this;
    }

    public function getApprovisionnement(): ?Approvisionnement
    {
        return $this->approvisionnement;
    }

    public function setApprovisionnement(Approvisionnement $approvisionnement): self
    {
        // set the owning side of the relation if necessary
        if ($approvisionnement->getProjet() !== $this) {
            $approvisionnement->setProjet($this);
        }

        $this->approvisionnement = $approvisionnement;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __toString()
    {
        return $this->getNom();
    }

    public function getTauxExecFin(): ?TauxExecFin
    {
        return $this->tauxExecFin;
    }

    public function setTauxExecFin(TauxExecFin $tauxExecFin): self
    {
        // set the owning side of the relation if necessary
        if ($tauxExecFin->getProjet() !== $this) {
            $tauxExecFin->setProjet($this);
        }

        $this->tauxExecFin = $tauxExecFin;

        return $this;
    }

    public function getTauxExecPhys(): ?TauxExecPhys
    {
        return $this->tauxExecPhys;
    }

    public function setTauxExecPhys(TauxExecPhys $tauxExecPhys): self
    {
        // set the owning side of the relation if necessary
        if ($tauxExecPhys->getProjet() !== $this) {
            $tauxExecPhys->setProjet($this);
        }

        $this->tauxExecPhys = $tauxExecPhys;

        return $this;
    }

    /**
     * @return Collection<int, Observation>
     */
    public function getObservations(): Collection
    {
        return $this->observations;
    }

    public function addObservation(Observation $observation): self
    {
        if (!$this->observations->contains($observation)) {
            $this->observations[] = $observation;
            $observation->setProjet($this);
        }

        return $this;
    }

    public function removeObservation(Observation $observation): self
    {
        if ($this->observations->removeElement($observation)) {
            // set the owning side to null (unless already changed)
            if ($observation->getProjet() === $this) {
                $observation->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProjet($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProjet() === $this) {
                $image->setProjet(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Depense>
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): self
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses[] = $depense;
            $depense->setProjet($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getProjet() === $this) {
                $depense->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(Plan $plan): self
    {
        if (!$this->plans->contains($plan)) {
            $this->plans[] = $plan;
            $plan->setProjet($this);
        }

        return $this;
    }

    public function removePlan(Plan $plan): self
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getProjet() === $this) {
                $plan->setProjet(null);
            }
        }

        return $this;
    }
}
