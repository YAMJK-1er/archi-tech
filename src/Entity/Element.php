<?php

namespace App\Entity;

use App\Repository\ElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElementRepository::class)]
class Element
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $stock_restant;

    #[ORM\ManyToOne(targetEntity: Approvisionnement::class, inversedBy: 'elements')]
    #[ORM\JoinColumn(nullable: false)]
    private $approvisionnement;

    #[ORM\Column(type: 'string', length: 255)]
    private $UniteOeuvre;

    #[ORM\OneToMany(mappedBy: 'element', targetEntity: Mouvement::class)]
    private $mouvements;

    public function __construct()
    {
        $this->mouvements = new ArrayCollection();
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

    public function getStockRestant(): ?int
    {
        return $this->stock_restant;
    }

    public function setStockRestant(int $stock_restant): self
    {
        $this->stock_restant = $stock_restant;

        return $this;
    }

    public function getApprovisionnement(): ?Approvisionnement
    {
        return $this->approvisionnement;
    }

    public function setApprovisionnement(?Approvisionnement $approvisionnement): self
    {
        $this->approvisionnement = $approvisionnement;

        return $this;
    }

    public function getUniteOeuvre(): ?string
    {
        return $this->UniteOeuvre;
    }

    public function setUniteOeuvre(string $UniteOeuvre): self
    {
        $this->UniteOeuvre = $UniteOeuvre;

        return $this;
    }

    /**
     * @return Collection<int, Mouvement>
     */
    public function getMouvements(): Collection
    {
        return $this->mouvements;
    }

    public function addMouvement(Mouvement $mouvement): self
    {
        if (!$this->mouvements->contains($mouvement)) {
            $this->mouvements[] = $mouvement;
            $mouvement->setElement($this);
        }

        return $this;
    }

    public function removeMouvement(Mouvement $mouvement): self
    {
        if ($this->mouvements->removeElement($mouvement)) {
            // set the owning side to null (unless already changed)
            if ($mouvement->getElement() === $this) {
                $mouvement->setElement(null);
            }
        }

        return $this;
    }
}
