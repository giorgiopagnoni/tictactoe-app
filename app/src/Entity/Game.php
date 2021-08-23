<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private string $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $winner;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private \DateTimeImmutable|null $closedAt;

    /**
     * @ORM\OneToMany(targetEntity=Move::class, mappedBy="game", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moves;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->moves = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(?int $winner): self
    {
        $this->winner = $winner;
        return $this;
    }

    public function isGameOver(): bool
    {
        return $this->closedAt !== null;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setClosedAt(\DateTimeImmutable $closedAt): self
    {
        $this->closedAt = $closedAt;
        return $this;
    }

    /**
     * @return Collection|Move[]
     */
    public function getMoves(): Collection
    {
        return $this->moves;
    }

    public function addMove(Move $move): self
    {
        if (!$this->moves->contains($move)) {
            $this->moves[] = $move;
            $move->setGame($this);
        }

        return $this;
    }

    public function removeMove(Move $move): self
    {
        if ($this->moves->removeElement($move)) {
            // set the owning side to null (unless already changed)
            if ($move->getGame() === $this) {
                $move->setGame(null);
            }
        }

        return $this;
    }
}
