<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adds audit columns (created_at, updated_at, created_by, updated_by) to any entity.
 *
 * The entity class must declare #[ORM\HasLifecycleCallbacks] for the PrePersist
 * and PreUpdate callbacks to fire automatically.
 * created_by and updated_by are populated by EntityLifecycleSubscriber.
 */
trait TimestampableTrait
{
    /** @var \DateTimeImmutable Date and time when the record was created. */
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var \DateTimeImmutable|null Date and time of the last update. */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var string|null Email of the user who created the record. */
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $createdBy = null;

    /** @var string|null Email of the user who last updated the record. */
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $updatedBy = null;

    /** Initialises createdAt on first persist. */
    #[ORM\PrePersist]
    public function initCreatedAt(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    /** Refreshes updatedAt on every update. */
    #[ORM\PreUpdate]
    public function refreshUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function getCreatedBy(): ?string { return $this->createdBy; }
    public function setCreatedBy(?string $createdBy): static { $this->createdBy = $createdBy; return $this; }

    public function getUpdatedBy(): ?string { return $this->updatedBy; }
    public function setUpdatedBy(?string $updatedBy): static { $this->updatedBy = $updatedBy; return $this; }
}
