<?php

namespace App\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Automatically sets createdBy and updatedBy on entities that expose those setters,
 * using the email of the currently authenticated user.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class EntityLifecycleSubscriber
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage) {}

    /** Sets createdBy and updatedBy when an entity is first persisted. */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $email  = $this->getCurrentUserEmail();

        if ($email === null) {
            return;
        }

        if (method_exists($entity, 'setCreatedBy')) {
            $entity->setCreatedBy($email);
        }
        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($email);
        }
    }

    /** Refreshes updatedBy whenever an entity is updated. */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $email  = $this->getCurrentUserEmail();

        if ($email !== null && method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($email);
        }
    }

    /** Returns the email of the authenticated user, or null if not logged in. */
    private function getCurrentUserEmail(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user->getUserIdentifier();
    }
}
