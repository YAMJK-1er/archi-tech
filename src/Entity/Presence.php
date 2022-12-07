<?php

namespace App\Entity;

use App\Repository\PresenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PresenceRepository::class)]
class Presence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'presences')]
    private $projet;

    #[ORM\OneToMany(mappedBy: 'presence', targetEntity: Ouvrier::class)]
    private $ouvriers;

    public function __construct()
    {
        $this->ouvriers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    /**
     * @return Collection<int, Ouvrier>
     */
    public function getOuvriers(): Collection
    {
        return $this->ouvriers;
    }

    public function addOuvrier(Ouvrier $ouvrier): self
    {
        if (!$this->ouvriers->contains($ouvrier)) {
            $this->ouvriers[] = $ouvrier;
            $ouvrier->setPresence($this);
        }

        return $this;
    }

    public function removeOuvrier(Ouvrier $ouvrier): self
    {
        if ($this->ouvriers->removeElement($ouvrier)) {
            // set the owning side to null (unless already changed)
            if ($ouvrier->getPresence() === $this) {
                $ouvrier->setPresence(null);
            }
        }

        return $this;
    }
}
