<?php

namespace App\Entity;

use App\Repository\BillingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BillingRepository::class)
 */
class Billing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", options={"default":0})
     */
    private $credit;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isAutoRenewal;

    /**
     * @ORM\ManyToOne(targetEntity=Plan::class, inversedBy="billings")
     */
    private $plan;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, mappedBy="billing", cascade={"persist", "remove"})
     */
    private $person;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $expiredAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(float $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsAutoRenewal(): ?bool
    {
        return $this->isAutoRenewal;
    }

    public function setIsAutoRenewal(bool $isAutoRenewal): self
    {
        $this->isAutoRenewal = $isAutoRenewal;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        // unset the owning side of the relation if necessary
        if ($person === null && $this->person !== null) {
            $this->person->setBilling(null);
        }

        // set the owning side of the relation if necessary
        if ($person !== null && $person->getBilling() !== $this) {
            $person->setBilling($this);
        }

        $this->person = $person;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}
