<?php

namespace App\Security;

use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * Voter that controls access to individual lesson pages.
 *
 * Grants the lesson_view attribute only to authenticated users who have
 * purchased the lesson directly or via its parent cursus.
 */
class LessonVoter extends Voter
{
    /** @var string Attribute checked by isGranted() calls on Lesson objects. */
    const VIEW = 'lesson_view';

    public function __construct(private PurchaseRepository $purchaseRepository) {}

    /**
     * Supports the lesson_view attribute on Lesson objects only.
     *
     * @param string $attribute The security attribute being voted on.
     * @param mixed  $subject   The object being secured.
     * @return bool True when this voter should handle the vote.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Lesson;
    }

    /**
     * Returns true if the current user has purchased this lesson or its cursus.
     *
     * @param string         $attribute The attribute being checked (always lesson_view here).
     * @param mixed          $subject   The Lesson entity being accessed.
     * @param TokenInterface $token     The current authentication token.
     * @param Vote|null      $vote      Optional vote object (Symfony 7 signature).
     * @return bool True when access is granted.
     */
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
