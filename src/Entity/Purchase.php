<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    const TYPE_CURSUS = 'cursus';
    const TYPE_LESSON = 'lesson';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Cursus::class, inversedBy: 'purchases')]
    private ?Cursus $cursus = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'purchases')]
    private ?Lesson $lesson = null;

    #[ORM\Column(length: 10)]
    private string $type = self::TYPE_CURSUS;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $stripePaymentId = null;

    #[ORM\Column]
    private \DateTimeImmutable $purchasedAt;

    public function __construct()
    {
        $this->purchasedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getCursus(): ?Cursus { return $this->cursus; }
    public function setCursus(?Cursus $cursus): static { $this->cursus = $cursus; return $this; }

    public function getLesson(): ?Lesson { return $this->lesson; }
    public function setLesson(?Lesson $lesson): static { $this->lesson = $lesson; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getAmount(): ?string { return $this->amount; }
    public function setAmount(string $amount): static { $this->amount = $amount; return $this; }

    public function getStripePaymentId(): ?string { return $this->stripePaymentId; }
    public function setStripePaymentId(?string $stripePaymentId): static { $this->stripePaymentId = $stripePaymentId; return $this; }

    public function getPurchasedAt(): \DateTimeImmutable { return $this->purchasedAt; }
}
