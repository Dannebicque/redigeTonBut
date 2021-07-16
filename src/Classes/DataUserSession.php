<?php


namespace App\Classes;


use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Repository\AnneeRepository;
use App\Repository\DepartementRepository;
use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;
    private ?Departement $departement;
    private AnneeRepository $anneeRepository;
    private DepartementRepository $departementRepository;
    private string $dir;


    public function __construct(TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        KernelInterface $kernel,
        DepartementRepository $departementRepository,
        AnneeRepository $anneeRepository) {
        $this->anneeRepository = $anneeRepository;
        $this->departementRepository = $departementRepository;
        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
            if (in_array('ROLE_GT', $tokenStorage->getToken()->getRoleNames())) {
                if ($session->get('departement') !== null) {
                    $this->departement = $departementRepository->find($session->get('departement'));
                } else {
                    $this->departement = null;
                }
            } else {
                $this->departement = $this->user->getDepartement();
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
        if ($this->departement !== null) {
            return $this->anneeRepository->findByDepartement($this->departement);
        }
        return null;
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

    public function version()
    {
        $filename = $this->dir.'/package.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }
}
