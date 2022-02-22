<?php

namespace App\Entity;

use App\Repository\BillingHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: BillingHistoryRepository::class)]
class BillingHistory
{
    final public const STATUS_PENDING = 'pending';
    final public const STATUS_PAID = 'paid';
    final public const STATUS_CANCELED = 'canceled';
    final public const STATUS_REFUNDED = 'refunded';
    final public const STATUS_FAILED = 'failed';
    final public const STATUS_UNKNOWN = 'unknown';
    final public const STATUS_PROCESSING = 'processing';
    final public const STATUS_COMPLETED = 'completed';
    final public const STATUS_INVALID = 'invalid';
    final public const TYPE_DEBIT = 'debit';
    final public const TYPE_CREDIT = 'credit';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'float')]
    private ?float $amount = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * The type of transaction. available values are: debit, credit.
     */
    #[ORM\Column(type: 'string', length: 180)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Billing::class, inversedBy: 'billingHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Billing $billing = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBilling(): ?Billing
    {
        return $this->billing;
    }

    public function setBilling(?Billing $billing): self
    {
        $this->billing = $billing;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
