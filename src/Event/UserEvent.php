<?php


namespace App\Event;


use App\Entity\User;

class UserEvent
{
    public const CREATION_COMPTE = 'creation.compte';

    protected User $user;
    protected ?string $password;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


}
