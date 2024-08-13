<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentaireRepository::class)
 */
class Commentaire
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $idCom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Contenu;

    /**
     * @ORM\Column(type="date")
     */
    private $Date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbr_likes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbr_reclamation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCom(): ?int
    {
        return $this->idCom;
    }

    public function setIdCom(int $idCom): self
    {
        $this->idCom = $idCom;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->Contenu;
    }

    public function setContenu(string $Contenu): self
    {
        $this->Contenu = $Contenu;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): self
    {
        $this->Date = $Date;

        return $this;
    }

    public function getNbrLikes(): ?int
    {
        return $this->nbr_likes;
    }

    public function setNbrLikes(?int $nbr_likes): self
    {
        $this->nbr_likes = $nbr_likes;

        return $this;
    }

    public function getNbrReclamation(): ?int
    {
        return $this->nbr_reclamation;
    }

    public function setNbrReclamation(?int $nbr_reclamation): self
    {
        $this->nbr_reclamation = $nbr_reclamation;

        return $this;
    }
}
