<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\PurchaseRequest;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var array<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PurchaseRequest>
     */
    #[ORM\OneToMany(mappedBy: 'requestedBy', targetEntity: PurchaseRequest::class)]
    private Collection $purchaseRequests;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->purchaseRequests = new ArrayCollection();
        $this->roles = []; // toujours initialisé
    }

    // ========================
    // Basic getters / setters
    // ========================
    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // au moins un rôle par défaut pour chaque utilisateur
        $roles[] = 'ROLE_STOREKEEPER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }
    #[\Deprecated] public function eraseCredentials(): void {}

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    // ========================
    // PurchaseRequests relation
    // ========================
    public function getPurchaseRequests(): Collection { return $this->purchaseRequests; }

    public function addPurchaseRequest(PurchaseRequest $purchaseRequest): static
    {
        if (!$this->purchaseRequests->contains($purchaseRequest)) {
            $this->purchaseRequests->add($purchaseRequest);
            $purchaseRequest->setRequestedBy($this);
        }
        return $this;
    }

    public function removePurchaseRequest(PurchaseRequest $purchaseRequest): static
    {
        if ($this->purchaseRequests->removeElement($purchaseRequest)) {
            if ($purchaseRequest->getRequestedBy() === $this) {
                $purchaseRequest->setRequestedBy(null);
            }
        }
        return $this;
    }

    // ========================
    // Role helpers
    // ========================
    public function isAdmin(): bool {
        return in_array('ROLE_ADMIN', $this->getRoles() ?: [], true);
    }

    public function isManager(): bool {
        return in_array('ROLE_MANAGER', $this->getRoles() ?: [], true);
    }

    public function isStorekeeper(): bool {
        return in_array('ROLE_STOREKEEPER', $this->getRoles() ?: [], true);
    }
}
