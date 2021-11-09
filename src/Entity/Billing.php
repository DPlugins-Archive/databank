<?php

namespace App\Entity;

use App\Repository\BillingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @ORM\OneToMany(targetEntity=BillingHistory::class, mappedBy="billing", orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $billingHistories;

    public function __construct()
    {
        $this->billingHistories = new ArrayCollection();
    }

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
        if (null === $person && null !== $this->person) {
            $this->person->setBilling(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $person && $person->getBilling() !== $this) {
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

    /**
     * @return Collection|BillingHistory[]
     */
    public function getBillingHistories(): Collection
    {
        return $this->billingHistories;
    }

    public function addBillingHistory(BillingHistory $billingHistory): self
    {
        if (!$this->billingHistories->contains($billingHistory)) {
            $this->billingHistories[] = $billingHistory;
            $billingHistory->setBilling($this);
        }

        return $this;
    }

    public function removeBillingHistory(BillingHistory $billingHistory): self
    {
        if ($this->billingHistories->removeElement($billingHistory)) {
            // set the owning side to null (unless already changed)
            if ($billingHistory->getBilling() === $this) {
                $billingHistory->setBilling(null);
            }
        }

        return $this;
    }
}
