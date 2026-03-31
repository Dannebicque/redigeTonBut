<?php


namespace App\Security;

use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\User;
use App\Entity\Version;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use \Symfony\Bundle\SecurityBundle\Security;

class FormationVoter extends Voter
{
    public const string FORMATION_VIEW = 'FORMATION_VIEW';

    public const string FORMATION_EDIT = 'FORMATION_EDIT';

    public const string FORMATION_DUPLICATE = 'FORMATION_DUPLICATE';

    public const string FORMATION_DELETE = 'FORMATION_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::FORMATION_VIEW, self::FORMATION_EDIT, self::FORMATION_DELETE, self::FORMATION_DUPLICATE])) {
            return false;
        }
        // only vote on `ApcRessource` or ApcRessource objects
        return $subject instanceof Version || $subject instanceof Departement || $subject instanceof Semestre || $subject instanceof ApcRessource || $subject instanceof ApcSae;
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
            case self::FORMATION_VIEW:
                return $this->canView($post, $user);
            case self::FORMATION_EDIT:
            case self::FORMATION_DUPLICATE:
                return $this->canEdit($post, $user);
            case self::FORMATION_DELETE:
                return $this->canDelete($post, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(
        Version|Departement|Semestre|ApcSae|ApcRessource $post, User $user): bool
    {
        return true;
    }

    private function canEdit(Version|Departement|Semestre|ApcSae|ApcRessource $post, User $user): bool
    {
        if (!in_array('ROLE_EDITEUR', $user->getRoles()) && !in_array('ROLE_PACD', $user->getRoles()) && !in_array('ROLE_CPN', $user->getRoles())) {
            return false;
        }

        if (!$user->getDepartement() instanceof Departement || !$this->getDepartementFromSubject($post) instanceof Departement) {
            return false;
        }


        // this assumes that the Post object has a `getOwner()` method
        if (in_array('ROLE_CPN', $user->getRoles()) || in_array('ROLE_EDITEUR', $user->getRoles())) {
            foreach ($user->getCpnDepartements() as $dpt) {
                if ($dpt->getId() === $this->getDepartementFromSubject($post)->getId()) {
                    return true;
                }
            }
        }

        return $user->getDepartement()->getId() === $this->getDepartementFromSubject($post)->getId();
    }

    private function canDelete(Version|Departement|Semestre|ApcSae|ApcRessource $post, User $user): bool
    {
        if (!in_array('ROLE_PACD', $user->getRoles()) && !in_array('ROLE_CPN', $user->getRoles())) {
            return false;
        }

        if (!$user->getDepartement() instanceof Departement || !$this->getDepartementFromSubject($post) instanceof Departement) {
            return false;
        }

        if (in_array('ROLE_CPN', $user->getRoles())) {
            foreach ($user->getCpnDepartements() as $dpt) {
                if ($dpt->getId() === $this->getDepartementFromSubject($post)->getId()) {
                    return true;
                }
            }
        }

        return $user->getDepartement()->getId() === $this->getDepartementFromSubject($post)->getId();
    }

    public function getDepartementFromSubject(Version|Departement|Semestre|ApcSae|ApcRessource $post): ?Departement
    {
        return $post->getVersion()?->getDepartement();
    }
}
