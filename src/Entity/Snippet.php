<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
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
 * Secured resource.
 *
 * @ORM\Entity(repositoryClass=SnippetRepository::class)
 */
#[ApiResource(
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
class Snippet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['snippet:read'])]
    #[ApiProperty(
        identifier: false,
        security: "is_granted('ROLE_ADMIN')",
    )]
    private int $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     */
    #[Assert\Uuid]
    #[Groups(['snippet:read'])]
    #[ApiProperty(identifier: true)]
    private Uuid $uuid;

    /**
     * The slug of plugin that own the snippet.
     *
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private string $namespace;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    private string $name;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SnippetIsPublicFilter::class)]
    private bool $isPublic;

    /**
     * @ORM\OneToMany(targetEntity=Blob::class, mappedBy="snippet", orphanRemoval=true)
     */
    #[Groups(['snippet:read'])]
    #[ApiProperty(push: true)]
    private array|ArrayCollection|Collection $blobs;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    #[Groups(['snippet:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, mappedBy="snippets")
     *
     * @var Tag[]|Collection Available tags for this snippet
     */
    #[Groups(['snippet:read'])]
    private iterable $tags;

    /**
     * The additional data about this snippet, all your apps extra data you can put in this field.
     *
     * @ORM\Column(type="json", nullable=true)
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    private ?array $meta = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['snippet:read', 'snippet:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    private ?string $description = null;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="snippets")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['snippet:read'])]
    #[ApiProperty(
        security: "is_granted('ROLE_USER') and object.getPerson() == user",
    )]
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
