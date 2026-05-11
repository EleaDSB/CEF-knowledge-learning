<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\LessonProgressRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tracks the completion status of a lesson for a given user.
 *
 * A unique constraint on (user_id, lesson_id) prevents duplicate records.
 * When isCompleted is set to true, completedAt is automatically stamped.
 */
#[ORM\Entity(repositoryClass: LessonProgressRepository::class)]
#[ORM\UniqueConstraint(name: 'user_lesson_unique', columns: ['user_id', 'lesson_id'])]
#[ORM\HasLifecycleCallbacks]
class LessonProgress
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'lessonProgresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'progresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isCompleted = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getLesson(): ?Lesson { return $this->lesson; }
    public function setLesson(?Lesson $lesson): static { $this->lesson = $lesson; return $this; }

    public function isCompleted(): bool { return $this->isCompleted; }
    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;
        if ($isCompleted && $this->completedAt === null) {
            $this->completedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
}
