<?php

namespace App\Security;

use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class LessonVoter extends Voter
{
    const VIEW = 'lesson_view';

    public function __construct(private PurchaseRepository $purchaseRepository) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Lesson;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Lesson $lesson */
        $lesson = $subject;

        return $this->purchaseRepository->userHasLesson($user, $lesson);
    }
}
