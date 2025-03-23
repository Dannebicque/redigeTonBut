<?php


namespace App\Classes;


use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Repository\AnneeRepository;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;
    private ?Departement $departement = null;
    private AnneeRepository $anneeRepository;
    private string $dir;
    /**
     * @var string[]
     */
    private array $roleName;


    public function __construct(TokenStorageInterface         $tokenStorage,
                                private RequestStack          $requestStack,
                                KernelInterface               $kernel,
                                private DepartementRepository $departementRepository,
                                AnneeRepository               $anneeRepository)
    {
        $this->anneeRepository = $anneeRepository;
        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
            $this->roleName = $tokenStorage->getToken()->getRoleNames();
            $this->getDepartement();
        }
    }

    public function getDepartement()
    {
        if (in_array('ROLE_IUT', $this->roleName) || in_array('ROLE_GT', $this->roleName) || in_array('ROLE_CPN', $this->roleName) || in_array('ROLE_CPN_LECTEUR', $this->roleName)) {
            if ($this->requestStack->getSession()->has('departement')) {
                $this->departement = $this->departementRepository->find($this->requestStack->getSession()->get('departement'));
            } else {
                $this->departement = null;
            }
        } else {
            $this->departement = $this->user->getDepartement();
        }

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
        $filename = $this->dir . '/package.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }

    /**
     * @return string|\Stringable|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser(): UserInterface|\Stringable|string
    {
        return $this->user;
    }


}
