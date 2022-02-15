<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\BlobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Blob resource.
 */
#[ApiResource(
    formats: ['jsonld'],
    collectionOperations: [
        'post' => [
            'security_post_denormalize' => "is_granted('ROLE_USER') and object.getSnippet().getPerson() == user",
        ],
    ],
    itemOperations: [
        'get' => ['security' => "object.getSnippet().getIsPublic() == true or (is_granted('ROLE_USER') and object.getSnippet().getPerson() == user)"],
        'put' => ['security' => "is_granted('ROLE_USER') and object.getSnippet().getPerson() == user"],
        'delete' => ['security' => "is_granted('ROLE_USER') and object.getSnippet().getPerson() == user"],
    ],
    normalizationContext: ['groups' => ['blob:read']],
    denormalizationContext: ['groups' => ['blob:write']],
    attributes: [
        'order' => ['revisions.createdAt' => 'ASC'],
    ],
)]
#[ORM\Entity(repositoryClass: BlobRepository::class)]
class Blob
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
     * The unique identifier of the blob resource.
     */
    #[Assert\Uuid]
    #[ApiProperty(identifier: true)]
    #[Groups(['blob:read', 'snippet:read'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /**
     * The snippet resource who own the blob resource.
     */
    #[Groups(['blob:read', 'blob:write'])]
    #[ORM\ManyToOne(targetEntity: Snippet::class, inversedBy: 'blobs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Snippet $snippet = null;

    /**
     * The revision resources that are associated with the blob resource. A blob resource can have many revision resources.
     */
    #[Groups(['blob:read'])]
    #[ApiSubresource]
    #[ORM\OneToMany(targetEntity: Revision::class, mappedBy: 'blob', orphanRemoval: true)]
    private array|ArrayCollection|Collection $revisions;

    /**
     * The hash of the blob resource.
     */
    #[Groups(['blob:read', 'snippet:read'])]
    #[ORM\Column(type: 'text')]
    private ?string $hash = null;

    #[Groups(['blob:read', 'snippet:read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['blob:read', 'snippet:read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'create', field: ['meta', 'content'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The size of the blob resource in bytes.
     */
    #[Groups(['blob:read', 'snippet:read'])]
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $size;

    /**
     * The additional data about the blob resource.
     */
    #[Groups(['blob:read', 'blob:write', 'snippet:read', 'snippet:write'])]
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $meta = [];

    /**
     * The content of the blob resource.
     */
    #[Assert\NotBlank]
    #[Groups(['blob:read', 'blob:write', 'snippet:write'])]
    #[ORM\Column(type: 'text')]
    private string $content;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->revisions = new ArrayCollection();
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

    public function getSnippet(): ?Snippet
    {
        return $this->snippet;
    }

    public function setSnippet(?Snippet $snippet): self
    {
        $this->snippet = $snippet;

        return $this;
    }

    /**
     * @return Collection|Revision[]
     */
    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(Revision $revision): self
    {
        if (!$this->revisions->contains($revision)) {
            $this->revisions[] = $revision;
            $revision->setBlob($this);
        }

        return $this;
    }

    public function removeRevision(Revision $revision): self
    {
        if ($this->revisions->removeElement($revision)) {
            // set the owning side to null (unless already changed)
            if ($revision->getBlob() === $this) {
                $revision->setBlob(null);
            }
        }

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

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

    /**
     * The excerpt of the blob resource. This is the first few lines of the blob's content for preview purposes.
     */
    #[Groups(['blob:read', 'snippet:read'])]
    public function getExcerpt(): ?string
    {
        return implode(PHP_EOL, array_slice(explode(PHP_EOL, $this->getContent()), 0, 50));
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        $this->setHash(sha1($content));
        $this->setSize((new ByteString($content))->length());

        return $this;
    }
}
