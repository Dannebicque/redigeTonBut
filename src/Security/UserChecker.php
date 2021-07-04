<?php
namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isActif()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas encore activé par votre PACD.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
//        if (!$user instanceof AppUser) {
//            return;
//        }
//
//        // user account is expired, the user may be notified
//        if ($user->isExpired()) {
//            throw new AccountExpiredException('...');
//        }
    }
}
