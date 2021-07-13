<?php


namespace App\Security;

use App\Entity\Annee;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GoodDepartementVoter extends Voter
{
    // these strings are just invented: you can use anything
    public const NEW = 'new';
    public const CONSULTE = 'consulte';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::NEW, self::CONSULTE])) {
            return false;
        }

        // only vote on `ApcRessource` or ApcRessource objects
        if (!($subject instanceof Semestre || $subject instanceof Annee || $subject instanceof Departement)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // ROLE_GT can do anything! The power!
        if ($this->security->isGranted('ROLE_GT')) {
            return true;
        }



        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        $post = $subject;

        switch ($attribute) {
            case self::NEW:
                return $this->canAdd($post, $user);
            case self::CONSULTE:
                return $this->canConsulte($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canConsulte(Semestre|Annee|Departement $post, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canAdd($post, $user)) {
            return true;
        }

        // the Post object could have, for example, a method `isPrivate()`
        //todo: ajouter champ blocage édition sur ??? département ??? tableaux ???
        return true;
    }

    private function canAdd(Semestre|Annee|Departement $post, User $user): bool
    {
        if (in_array('ROLE_LECTEUR', $user->getRoles())) {
            return false;
        }

        if ($user->getDepartement() === null) {
            return false;
        }

        if ($post instanceof Departement) {
            return $user->getDepartement()->getId() === $post->getId();
        }

        if ($post instanceof Annee && $post->getDepartement() !== null) {
            return $user->getDepartement()->getId() === $post->getDepartement()->getId();
        }

        if ($post instanceof Semestre) {
            return $user->getDepartement()->getId() === $post->getAnnee()->getDepartement()->getId();
        }



        // this assumes that the Post object has a `getOwner()` method
        return false;
    }
}
