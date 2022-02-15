<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RevisionRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Revision resource.
 */
#[ApiResource(
    formats: ['jsonld'],
    collectionOperations: [],
    itemOperations: [
        'get' => ['security' => "object.getBlob().getSnippet().getIsPublic() == true or (is_granted('ROLE_USER') and object.getBlob().getSnippet().getPerson() == user)"],
    ],
    normalizationContext: ['groups' => ['revision:read']],
)]
#[ORM\Entity(repositoryClass: RevisionRepository::class)]
class Revision
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
     * The unique identifier of the revision resource.
     */
    #[Assert\Uuid]
    #[ApiProperty(identifier: true)]
    #[Groups(['blob:read', 'revision:read'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /**
     * The blob resource who own the revision resource.
     */
    #[ORM\ManyToOne(targetEntity: Blob::class, inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Blob $blob = null;

    /**
     * The hash of the revision resource.
     */
    #[Groups(['blob:read', 'revision:read'])]
    #[ORM\Column(type: 'text')]
    private ?string $hash = null;

    /**
     * @Gedmo\Timestampable(on="create")
     */
    #[Groups(['blob:read', 'revision:read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The size of the revision resource in bytes.
     */
    #[Groups(['blob:read', 'revision:read'])]
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $size;

    /**
     * The content of the revision resource.
     */
    #[Groups(['revision:read'])]
    #[ORM\Column(type: 'text')]
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

    /**
     * The excerpt of the revision resource. This is the first few lines of the revision's content for preview purposes.
     */
    #[Groups(['blob:read', 'revision:read'])]
    public function getExcerpt(): ?string
    {
        return implode(PHP_EOL, array_slice(explode(PHP_EOL, $this->getContent()), 0, 50));
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        $this->setHash(sha1($content));
        $this->setSize((new ByteString($content))->length());

        return $this;
    }
}
