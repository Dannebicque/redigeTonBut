<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/BaseController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 24/05/2021 16:35
 */

namespace App\Controller;

use App\Classes\DataUserSession;
use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Entity\User;
use App\Entity\Version;
use App\Repository\DepartementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Class BaseController.
 */
class BaseController extends AbstractController
{
    protected EntityManagerInterface $entityManager;

    protected DepartementRepository $dptRepository;

    protected TranslatorInterface $translator;

    protected ?FlashBagInterface $flashBag = null;

    protected ?SessionInterface $session = null;

    protected RequestStack $requestStack;

    private ?Departement $departement = null;

    protected DataUserSession $dataUserSession;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    #[Required]
    public function setDataUserSession(DataUserSession $dataUserSession): void
    {
        $this->dataUserSession = $dataUserSession;
    }

    #[Required]
    public function setDepartementRepository(DepartementRepository $dptRepository): void
    {
        $this->dptRepository = $dptRepository;
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getSession(): ?SessionInterface
    {
        if ($this->session !== null) {
            return $this->session;
        }

        if (isset($this->requestStack) && $this->requestStack->getMainRequest()?->hasSession()) {
            return $this->session = $this->requestStack->getSession();
        }

        return null;
    }

    protected function getFlashBag(): ?FlashBagInterface
    {
        if ($this->flashBag !== null) {
            return $this->flashBag;
        }

        $session = $this->getSession();
        if ($session !== null && method_exists($session, 'getFlashBag')) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $session->getFlashBag();
            return $this->flashBag = $flashBag;
        }

        return null;
    }

    public function getCaracteristiques(): Tertiaire|Secondaire|null
    {
        $departement = $this->resolveDepartement();

        if ($departement?->isTertiaire()) {
            return new Tertiaire();
        }

        if ($departement?->isSecondaire()) {
            return new Secondaire();
        }
        return null;
    }

    private function resolveDepartement(): ?Departement
    {
        if (
            $this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_GT') || $this->isGranted('ROLE_EDITEUR') || $this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_IUT') || $this->isGranted('ROLE_CPN_LECTEUR')
        ) {
            $session = $this->getSession();
            if ($session !== null && $session->get('departement') !== null) {
                return $this->dptRepository->find($session->get('departement'));
            }

            return null;
        }

        $user = $this->getUser();
        if ($user instanceof User && $user->getDepartement() !== null) {
            return $user->getDepartement();
        }

        return null;
    }

    public function getDepartement(): Departement|RedirectResponse
    {
        $this->departement = $this->resolveDepartement();

        if ($this->departement === null) {
            $this->addFlashBag('warning', 'Veuillez choisir un departement pour continuer.');

            if (
                $this->isGranted('ROLE_ADMIN') ||
                $this->isGranted('ROLE_GT') || $this->isGranted('ROLE_EDITEUR') || $this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_IUT') || $this->isGranted('ROLE_CPN_LECTEUR')
            ) {
                return $this->redirectToRoute('homepage_specialite');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->departement;
    }

    public function addFlashBag(string $type, string $message): void
    {
        $flashBag = $this->getFlashBag();
        if ($flashBag !== null) {
            $flashBag->add($type, $message);
        } else {
            $this->addFlash($type, $message);
        }
    }

    public function getDataUserSession(): DataUserSession
    {
        return $this->dataUserSession;
    }

    public function getVersion() : ?Version
    {
        return $this->getDataUserSession()->getVersion();
    }


}
