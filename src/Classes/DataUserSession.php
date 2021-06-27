<?php


namespace App\Classes;


use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Repository\AnneeRepository;
use App\Repository\DepartementRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;
    private Departement $departement;
    private AnneeRepository $anneeRepository;
    private DepartementRepository $departementRepository;

    public function __construct(TokenStorageInterface $tokenStorage,
        DepartementRepository $departementRepository,
        AnneeRepository $anneeRepository) {
        $this->anneeRepository = $anneeRepository;
        $this->departementRepository = $departementRepository;

        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
            if (count($this->user->getDepartements()) === 1) {
                $this->departement  = $this->user->getDepartements()[0];
            } else {
                $this->departement = $this->user->getPersonnelDepartements()[0];
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
