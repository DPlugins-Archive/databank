<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RevisionRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Revision resource.
 * 
 * @ORM\Entity(repositoryClass=RevisionRepository::class)
 */
#[ApiResource(
    collectionOperations: [],
    itemOperations: [
        'get' => ['security' => "object.getBlob().getSnippet().getIsPublic() == true or (is_granted('ROLE_USER') and object.getBlob().getSnippet().getPerson() == user)"],
    ],
    normalizationContext: ['groups' => ['revision:read']],
)]
class Revision
{
    /**
     * The id of record in the database.
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[ApiProperty(identifier: false)]
    private int $id;

    /**
     * The unique identifier of the revision resource.
     * 
     * @ORM\Column(type="uuid", unique=true)
     */
    #[Assert\Uuid]
    #[ApiProperty(identifier: true)]
    #[Groups(['blob:read', 'revision:read'])]
    private Uuid $uuid;

    /**
     * The blob resource who own the revision resource.
     * 
     * @ORM\ManyToOne(targetEntity=Blob::class, inversedBy="revisions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Blob $blob = null;

    /**
     * The hash of the revision resource.
     * 
     * @ORM\Column(type="text")
     */
    #[Groups(['blob:read', 'revision:read'])]
    private ?string $hash = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    #[Groups(['blob:read', 'revision:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The size of the revision resource in bytes.
     * 
     * @ORM\Column(type="integer", options={"default":0})
     */
    #[Groups(['blob:read', 'revision:read'])]
    private int $size;

    /**
     * The excerpt of the revision resource. This is the first few lines of the revision's content for preview purposes.
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(['blob:read', 'revision:read'])]
    private string $excerpt;

    /**
     * The content of the revision resource.
     * 
     * @ORM\Column(type="text")
     */
    #[Groups(['revision:read'])]
    private string $content;

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

    public function getBlob(): ?Blob
    {
        return $this->blob;
    }

    public function setBlob(?Blob $blob): self
    {
        $this->blob = $blob;

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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
