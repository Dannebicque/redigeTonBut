<?php


namespace App\Security;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\ApcSituationProfessionnelle;
use App\Entity\Departement;
use App\Entity\User;
use App\Entity\Version;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CompetencesVoter extends Voter
{
    // these strings are just invented: you can use anything
    public const COMPETENCES_VIEW = 'COMPETENCES_VIEW';

    public const COMPETENCES_EDIT = 'COMPETENCES_EDIT';

    public const COMPETENCES_DUPLICATE = 'COMPETENCES_DUPLICATE';

    public const COMPETENCES_DELETE = 'COMPETENCES_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::COMPETENCES_VIEW, self::COMPETENCES_EDIT, self::COMPETENCES_DELETE, self::COMPETENCES_DUPLICATE])) {
            return false;
        }
        // only vote on `ApcRessource` or ApcRessource objects
        return $subject instanceof Version || $subject instanceof Departement || $subject instanceof ApcCompetence || $subject instanceof ApcComposanteEssentielle || $subject instanceof ApcParcours || $subject instanceof ApcSituationProfessionnelle || $subject instanceof ApcNiveau || $subject instanceof ApcApprentissageCritique;
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
            case self::COMPETENCES_VIEW:
                return $this->canView($post, $user);
            case self::COMPETENCES_EDIT:
            case self::COMPETENCES_DUPLICATE:
                return $this->canEdit($post, $user);
            case self::COMPETENCES_DELETE:
                return $this->canDelete($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(
        ApcCompetence|ApcComposanteEssentielle|ApcParcours|ApcSituationProfessionnelle|ApcNiveau|ApcApprentissageCritique $post, User $user): bool
    {
        return true;
    }

    private function canEdit(ApcCompetence|ApcComposanteEssentielle|ApcParcours|ApcSituationProfessionnelle|ApcNiveau|ApcApprentissageCritique|Departement $post, User $user): bool
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

    private function canDelete(ApcCompetence|ApcComposanteEssentielle|ApcParcours|ApcSituationProfessionnelle|ApcNiveau|ApcApprentissageCritique $post, User $user): bool
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

        // this assumes that the Post object has a `getOwner()` method
        return $user->getDepartement()->getId() === $this->getDepartementFromSubject($post)->getId();
    }

    public function getDepartementFromSubject(ApcCompetence|ApcComposanteEssentielle|ApcParcours|ApcSituationProfessionnelle|ApcNiveau|ApcApprentissageCritique|Departement $post): ?Departement
    {
        if ($post instanceof ApcCompetence) {
            return $post->getDepartement();
        }

        if ($post instanceof ApcApprentissageCritique) {
            return $post->getDepartement();
        }

        if ($post instanceof Departement) {
            return $post;
        }

        if ($post instanceof ApcComposanteEssentielle) {
            return $post->getDepartement();
        }

        if ($post instanceof ApcParcours) {
            return $post->getDepartement();
        }

        if ($post instanceof ApcSituationProfessionnelle) {
            return $post->getDepartement();
        }

        if ($post instanceof ApcNiveau) {
            return $post->getDepartement();
        }

        return null;
    }
}
