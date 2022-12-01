<?php

namespace App\Entity;

use App\Repository\ElementRepository;
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

    #[ORM\Column(type: 'integer')]
    private $stock_restant;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $quantite_globale;

    #[ORM\ManyToOne(targetEntity: Approvisionnement::class, inversedBy: 'elements')]
    #[ORM\JoinColumn(nullable: false)]
    private $approvisionnement;

    #[ORM\Column(type: 'string', length: 255)]
    private $UniteOeuvre;

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

    public function getQuantiteGlobale(): ?int
    {
        return $this->quantite_globale;
    }

    public function setQuantiteGlobale(?int $quantite_globale): self
    {
        $this->quantite_globale = $quantite_globale;

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
}
