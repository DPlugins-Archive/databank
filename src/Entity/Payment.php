<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=BillingHistory::class, mappedBy="payment", cascade={"persist", "remove"})
     */
    private $billingHistory;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBillingHistory(): ?BillingHistory
    {
        return $this->billingHistory;
    }

    public function setBillingHistory(?BillingHistory $billingHistory): self
    {
        // unset the owning side of the relation if necessary
        if (null === $billingHistory && null !== $this->billingHistory) {
            $this->billingHistory->setPayment(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $billingHistory && $billingHistory->getPayment() !== $this) {
            $billingHistory->setPayment($this);
        }

        $this->billingHistory = $billingHistory;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
