<?php

namespace App\EventSubscriber;

use App\Event\UserEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class UserCreateCompteSubscriber implements EventSubscriberInterface
{
    protected MailerInterface $mailer;

    public static function getSubscribedEvents()
    {
        return [
            UserEvent::CREATION_COMPTE => 'onCreationCompte',
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
}
