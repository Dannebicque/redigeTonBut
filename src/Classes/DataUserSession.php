<?php


namespace App\Classes;


use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Entity\Version;
use App\Repository\AnneeRepository;
use App\Repository\DepartementRepository;
use App\Repository\VersionRepository;
use Exception;
use Stringable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
    private array $roleName = [];


    public function __construct(TokenStorageInterface                  $tokenStorage,
                                private readonly RequestStack          $requestStack,
                                KernelInterface                        $kernel,
                                private readonly DepartementRepository $departementRepository,
                                private readonly VersionRepository     $versionRepository,
                                AnneeRepository                        $anneeRepository)
    {
        $this->anneeRepository = $anneeRepository;
        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() instanceof TokenInterface) {
            $this->user = $tokenStorage->getToken()->getUser();
            $this->roleName = $tokenStorage->getToken()->getRoleNames();
            $this->getDepartement();
        }
    }

    public function getDepartement()
    {
        if (in_array('ROLE_ADMIN', $this->roleName) || in_array('ROLE_IUT', $this->roleName) || in_array('ROLE_GT', $this->roleName) || in_array('ROLE_CPN', $this->roleName) || in_array('ROLE_EDITEUR', $this->roleName) || in_array('ROLE_CPN_LECTEUR', $this->roleName)) {
            if ($this->requestStack->getSession()->has('departement')) {
                $this->departement = $this->departementRepository->find($this->requestStack->getSession()->get('departement'));
            } else {
                $this->departement = null;
            }
        } else {
            $this->departement = $this->user->getDepartement();
            $this->requestStack->getSession()->set('departement', $this->departement->getId());
        }

        return $this->departement;
    }

    public function getSpecialites()
    {
        return $this->departementRepository->findAll();
    }

    public function getSpecialitesActifs() : array
    {
        return $this->departementRepository->findByActifs();
    }

    public function getVersionOfSpecialites(int $annee): array
    {
        if ($annee === 2021 || $annee === 2027) {
            return $this->versionRepository->findByVersion($annee);
        }

        throw new Exception('Annee inconnue');
    }

    public function getAnnees()
    {
        if ($this->departement instanceof Departement) {
            return $this->anneeRepository->findByVersion($this->getVersion());
        }

        return null;
    }

    public function getCaracteristiques(): Tertiaire|Secondaire|null
    {
        if ($this->getDepartement()?->isTertiaire()) {
            return new Tertiaire();
        }

        if ($this->getDepartement()?->isSecondaire()) {
            return new Secondaire();
        }

        return null;
    }

    public function versionLogiciel()
    {
        $filename = $this->dir . '/package.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }

    public function getUser(): UserInterface|Stringable|string
    {
        return $this->user;
    }

    public function getVersion(): ?Version
    {
        if ($this->getDepartement() !== null) {
            return $this->versionRepository->findOneByAnneeAndDepartement($this->getDepartement(), $this->versionPn());
        }

        return null;
    }

    public function versionPn(): int
    {
        //on récupére dans la session, sinon 2021 par défaut
        return $this->requestStack->getSession()->get('versionPn', 2021);
    }


}
