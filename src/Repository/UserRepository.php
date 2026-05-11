<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Repository for User entities.
 *
 * Implements PasswordUpgraderInterface to rehash passwords on the fly when
 * the hashing algorithm changes, and provides token-based lookup for the
 * email-activation flow.
 *
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Rehashes and persists a user's password when the algorithm is upgraded.
     *
     * @param PasswordAuthenticatedUserInterface $user            The user whose password must be upgraded.
     * @param string                             $newHashedPassword The new hashed password value.
     * @throws UnsupportedUserException When the provided user is not an App\Entity\User instance.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }
        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Finds a user by their email-activation token.
     *
     * @param string $token The activation token sent by email.
     * @return User|null The matching user, or null if the token does not exist.
     */
    public function findByActivationToken(string $token): ?User
    {
        return $this->findOneBy(['activationToken' => $token]);
    }
}
