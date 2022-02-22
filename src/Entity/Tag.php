<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tag resource.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    /**
     * The id of record in the database.
     */
    #[ApiProperty(identifier: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * The unique identifier of the tag resource.
     */
    #[Assert\Uuid]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /**
     * The name of the tag resource.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    /**
     * The snippet resources that are associated with the tag resource. A tag resource can have many snippet resources and vice versa.
     */
    #[ORM\ManyToMany(targetEntity: Snippet::class, inversedBy: 'tags')]
    private array|ArrayCollection|Collection $snippets;

    /**
     * The owner of the snippet resource.
     */
    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'tags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    public function __construct()
    {
        $this->snippets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Snippet[]
     */
    public function getSnippets(): Collection
    {
        return $this->snippets;
    }

    public function addSnippet(Snippet $snippet): self
    {
        if (!$this->snippets->contains($snippet)) {
            $this->snippets[] = $snippet;
        }

        return $this;
    }

    public function removeSnippet(Snippet $snippet): self
    {
        $this->snippets->removeElement($snippet);

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}
