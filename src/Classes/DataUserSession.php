<?php


namespace App\Classes;


use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Repository\AnneeRepository;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;
    private ?Departement $departement;
    private AnneeRepository $anneeRepository;
    private DepartementRepository $departementRepository;

    public function __construct(TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        DepartementRepository $departementRepository,
        AnneeRepository $anneeRepository) {
        $this->anneeRepository = $anneeRepository;
        $this->departementRepository = $departementRepository;

        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
            if (count($this->user->getDepartements()) === 1) {
                $this->departement  = $this->user->getDepartements()[0];
            } else {
                if (count($this->user->getPersonnelDepartements()) > 0) {
                    $this->departement = $this->user->getPersonnelDepartements()[0];
                } else {
                    if ($session->get('departement') !== null) {
                        $this->departement = $departementRepository->find($session->get('departement'));
                    } else {
                        $this->departement = null;
                    }
                }
            }
        }
    }

    public function getDepartement()
    {
        return $this->departement;
    }

    public function getSpecialites()
    {
        return $this->departementRepository->findAll();
    }

    public function getAnnees()
    {
        return $this->anneeRepository->findByDepartement($this->departement);
    }

    public function getCaracteristiques()
    {
        if ($this->getDepartement()->isTertiaire()) {
            return new Tertiaire();
        }

        if ($this->getDepartement()->isSecondaire()) {
            return new Secondaire();
        }

        return null;
    }
}
