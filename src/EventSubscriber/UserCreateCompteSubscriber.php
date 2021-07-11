<?php

namespace App\EventSubscriber;

use App\Event\UserEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class UserCreateCompteSubscriber implements EventSubscriberInterface
{
    protected MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    public static function getSubscribedEvents()
    {
        return [
            UserEvent::CREATION_COMPTE => 'onCreationCompte',
            UserEvent::INIT_PASSWORD => 'onInitPassword',
        ];
    }

    public function onCreationCompte(UserEvent $userEvent)
    {
        $user =$userEvent->getUser();
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('[ORéBUT] Un compte a été cré sur l\'application')
            ->htmlTemplate('registration/creation_compte_email.html.twig')
            ->context(['user' => $user, 'password' => $userEvent->getPassword()]);
        $this->mailer->send($email);
    }

    public function onInitPassword(UserEvent $userEvent)
    {
        $user =$userEvent->getUser();
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('[ORéBUT] Mot de passe réinitialisé')
            ->htmlTemplate('registration/password_reinit_email.html.twig')
            ->context(['user' => $user, 'password' => $userEvent->getPassword()]);
        $this->mailer->send($email);
    }
}
