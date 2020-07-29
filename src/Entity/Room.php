<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 */
class Room
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="string")
     */
    private $Image;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;


    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $Height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $Depth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $Width;

    /**
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="Room", orphanRemoval=true, cascade={"persist"})
     */
    private $Items;

     /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $Public;


    public function __construct()
    {
        $this->created = new \DateTime();
        $this->Items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function getImage(): ?string
    {
        return $this->Image;
    }

    public function setImage(string $Image): self
    {
        $this->Image = $Image;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(string $UserId): self
    {
        $this->userId = $UserId;

        return $this;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->Height;
    }

    public function setHeight(?int $Height): self
    {
        $this->Height = $Height;

        return $this;
    }

    public function getDepth(): ?int
    {
        return $this->Depth;
    }

    public function setDepth(?int $Depth): self
    {
        $this->Depth = $Depth;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->Width;
    }

    public function setWidth(?int $Width): self
    {
        $this->Width = $Width;

        return $this;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems()
    {
        return $this->Items;
    }

    public function setItems($items): void
    {
        $this->Items = $items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->Items->contains($item)) {
            $this->Items[] = $item;
            $item->setRoom($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->Items->contains($item)) {
            $this->Items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getRoom() === $this) {
                $item->setRoom(null);
            }
        }

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->Public;
    }

    public function setPublic(?bool $Public): self
    {
        $this->Public = $Public;

        return $this;
    }
}
