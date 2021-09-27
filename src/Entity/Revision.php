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
 * @ORM\Entity(repositoryClass=RevisionRepository::class)
 */
#[ApiResource(
    collectionOperations: [],
    itemOperations: [
        "get" => ["security" => "object.getBlob().getSnippet().getIsPublic() == true or (is_granted('ROLE_USER') and object.getBlob().getSnippet().getPerson() == user)",],
    ],
    normalizationContext: ["groups" => ["revision:read"],],
)]
class Revision
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[ApiProperty(identifier: false)]
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     */
    #[Assert\Uuid]
    #[ApiProperty(identifier: true)]
    #[Groups(["blob:read", "revision:read",])]
    private Uuid $uuid;

    /**
     * @ORM\ManyToOne(targetEntity=Blob::class, inversedBy="revisions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $blob;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(["blob:read", "revision:read",])]
    private $hash;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    #[Groups(["blob:read", "revision:read",])]
    private $createdAt;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    #[Groups(["blob:read", "revision:read",])]
    private $size;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(["blob:read", "revision:read",])]
    private $excerpt;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(["revision:read",])]
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid()
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
