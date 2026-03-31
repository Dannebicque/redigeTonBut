<?php


namespace App\Security;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\User;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReferentielVoter extends Voter
{
    // these strings are just invented: you can use anything
    public const string VIEW = 'view';

    public const string EDIT = 'edit';

    public const string DUPLICATE = 'duplicate';

    public const string DELETE = 'delete';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::DUPLICATE])) {
            return false;
        }
        // only vote on `ApcRessource` or ApcRessource objects
        return $subject instanceof ApcRessource || $subject instanceof ApcSae || $subject instanceof ApcApprentissageCritique;
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
            case self::VIEW:
                return $this->canView($post, $user);
            case self::EDIT:
            case self::DUPLICATE:
                return $this->canEdit($post, $user);
            case self::DELETE:
                return $this->canDelete($post, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(ApcSae|ApcRessource $post, User $user): bool
    {
        return true;
    }

    private function canEdit(ApcSae|ApcRessource $post, User $user): bool
    {
        if (in_array('ROLE_LECTEUR', $user->getRoles()) || in_array('ROLE_CPN_LECTEUR', $user->getRoles())) {
            return false;
        }

        if (!$user->getDepartement() instanceof Departement || !$post->getVersion()->getDepartement() instanceof Departement) {
            return false;
        }

        // this assumes that the Post object has a `getOwner()` method
        if (in_array('ROLE_CPN', $user->getRoles())) {
            foreach ($user->getCpnDepartements() as $dpt) {
                if ($dpt->getId() === $post->getVersion()->getDepartement()->getId()) {
                    return true;
                }
            }
        }

        return $user->getDepartement()->getId() === $post->getVersion()->getDepartement()->getId();
    }

    private function canDelete(ApcSae|ApcRessource|ApcApprentissageCritique $post, User $user): bool
    {
        if (!in_array('ROLE_PACD', $user->getRoles()) && !in_array('ROLE_CPN', $user->getRoles())) {
            return false;
        }

        if (!$user->getDepartement() instanceof Departement || !$post->getVersion()->getDepartement() instanceof Departement) {
            return false;
        }

        if (in_array('ROLE_CPN', $user->getRoles())) {
            foreach ($user->getCpnDepartements() as $dpt) {
                if ($dpt->getId() === $post->getVersion()->getDepartement()->getId()) {
                    return true;
                }
            }
        }

        // this assumes that the Post object has a `getOwner()` method
        return $user->getDepartement()->getId() === $post->getVersion()->getDepartement()->getId();
    }
}
