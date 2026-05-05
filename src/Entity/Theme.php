<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 120, unique: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'theme', cascade: ['persist', 'remove'])]
    private Collection $cursus;

    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'theme')]
    private Collection $certifications;

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
        $this->certifications = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getCursus(): Collection { return $this->cursus; }
    public function getCertifications(): Collection { return $this->certifications; }

    public function getTotalLessons(): int
    {
        $total = 0;
        foreach ($this->cursus as $cursus) {
            $total += $cursus->getLessons()->count();
        }
        return $total;
    }
}
