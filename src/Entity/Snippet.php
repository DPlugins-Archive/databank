<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Filter\SnippetIsPublicFilter;
use App\Repository\SnippetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Snippet resource.
 */
#[ApiResource(
    formats: ['jsonld'],
    attributes: ['security' => "is_granted('ROLE_USER')"],
    collectionOperations: ['get', 'post'],
    itemOperations: [
        'get' => ['security' => "object.getIsPublic() == true or (is_granted('ROLE_USER') and object.getPerson() == user)"],
        'put' => ['security' => "is_granted('ROLE_USER') and object.getPerson() == user"],
        'delete' => ['security' => "is_granted('ROLE_USER') and object.getPerson() == user"],
    ],
    normalizationContext: ['groups' => ['snippet:read']],
    denormalizationContext: ['groups' => ['snippet:write']],
)]
#[ORM\Entity(repositoryClass: SnippetRepository::class)]
class Snippet
{
    /**
     * The id of record in the database.
     */
    #[Groups(['snippet:read'])]
    #[ApiProperty(
        identifier: false,
        security: "is_granted('ROLE_ADMIN')",
    )]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * The unique identifier of the snippet resource.
     */
    #[Assert\Uuid]
    #[Groups(['snippet:read'])]
    #[ApiProperty(identifier: true)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /**
     * The slug of plugin/vendor that own the snippet resource.
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ORM\Column(type: 'string', length: 255)]
    private string $namespace;

    /**
     * The name of the snippet resource.
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    /**
     * Is the snippet resource accessible to the public? default: false.
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SnippetIsPublicFilter::class)]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPublic;

    /**
     * The blob resources that are associated with the snippet resource. A snippet resource can have many blob resources.
     */
    #[ApiSubresource]
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ORM\OneToMany(targetEntity: Blob::class, mappedBy: 'snippet', orphanRemoval: true, cascade: ['persist'])]
    private array|ArrayCollection|Collection $blobs;

    #[Groups(['snippet:read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The tag resources that are associated with the snippet resource. A snippet resource can have many tag resources and vice versa.
     *
     * @var Tag[]|Collection
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'snippets')]
    private iterable $tags;

    /**
     * The additional data about the snippet resource.
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $meta = [];

    /**
     * The description of the snippet resource. Recommend to use Markdown syntax.
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * The owner of the snippet resource.
     */
    #[Groups(['snippet:read'])]
    #[ApiProperty(
        security: "is_granted('ROLE_USER') and object.getPerson() == user",
    )]
    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'snippets')]
    #[ORM\JoinColumn(nullable: false)]
    private Person $person;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->blobs = new ArrayCollection();
        $this->tags = new ArrayCollection();
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

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

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

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return Collection|Blob[]
     */
    public function getBlobs(): Collection
    {
        return $this->blobs;
    }

    public function addBlob(Blob $blob): self
    {
        if (!$this->blobs->contains($blob)) {
            $this->blobs[] = $blob;
            $blob->setSnippet($this);
        }

        return $this;
    }

    public function removeBlob(Blob $blob): self
    {
        if ($this->blobs->removeElement($blob)) {
            // set the owning side to null (unless already changed)
            if ($blob->getSnippet() === $this) {
                $blob->setSnippet(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addSnippet($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeSnippet($this);
        }

        return $this;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
