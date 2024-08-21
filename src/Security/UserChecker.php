<?php
namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(private RouterInterface $router)
    {
    }
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isActif()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas encore activé par votre PACD.');
        }

        if (!$user->isVerified()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Vous n\'avez pas confirmé votre adresse email. Veuillez vérifier votre boîte mail. <a href="'.$this->router->generate('app_confirmation_email', ['email' => $user->getEmail()]).'">Renvoyer le mail de confirmation</a>');
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
