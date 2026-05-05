<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
#[ORM\UniqueConstraint(name: 'user_theme_unique', columns: ['user_id', 'theme_id'])]
class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theme $theme = null;

    #[ORM\Column]
    private \DateTimeImmutable $obtainedAt;

    public function __construct()
    {
        $this->obtainedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getTheme(): ?Theme { return $this->theme; }
    public function setTheme(?Theme $theme): static { $this->theme = $theme; return $this; }

    public function getObtainedAt(): \DateTimeImmutable { return $this->obtainedAt; }
}
