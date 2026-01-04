<?php

namespace App\Entity;

use App\Repository\SupplierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PurchaseRequest;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    /**
     * @var Collection<int, PurchaseRequest>
     */
    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: PurchaseRequest::class)]
    private Collection $purchaseRequests;

    public function __construct()
    {
        $this->purchaseRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return Collection<int, PurchaseRequest>
     */
    public function getPurchaseRequests(): Collection
    {
        return $this->purchaseRequests;
    }

    public function addPurchaseRequest(PurchaseRequest $purchaseRequest): static
    {
        if (!$this->purchaseRequests->contains($purchaseRequest)) {
            $this->purchaseRequests->add($purchaseRequest);
            $purchaseRequest->setSupplier($this);
        }

        return $this;
    }

    public function removePurchaseRequest(PurchaseRequest $purchaseRequest): static
    {
        if ($this->purchaseRequests->removeElement($purchaseRequest)) {
            if ($purchaseRequest->getSupplier() === $this) {
                $purchaseRequest->setSupplier(null);
            }
        }

        return $this;
    }
}
