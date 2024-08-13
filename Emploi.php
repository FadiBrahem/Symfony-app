<?php

namespace App\Entity;

use App\Repository\EmploiRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmploiRepository::class)
 */
class Emploi extends Offre
{


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $typeContrat;

    /**
     * @ORM\Column(type="float")
     */
    private $salaire;



    public function getTypeContrat(): ?string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(string $typeContrat): self
    {
        $this->typeContrat = $typeContrat;

        return $this;
    }

    public function getSalaire(): ?float
    {
        return $this->salaire;
    }

    public function setSalaire(float $salaire): self
    {
        $this->salaire = $salaire;

        return $this;
    }
}
