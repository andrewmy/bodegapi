<?php

declare(strict_types=1);

namespace App\EventSubscriber\Traits;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @property TokenStorageInterface $tokenStorage
 */
trait HasUser
{
    private function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        return ($user instanceof User) ? $user : null;
    }
}
