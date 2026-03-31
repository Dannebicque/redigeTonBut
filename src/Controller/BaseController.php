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

    protected FlashBagInterface $flashBag;

    protected SessionInterface $session;

    private ?Departement $departement;

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
    public function setSession(RequestStack $session): void
    {
        $this->session = $session->getSession();
    }

    #[Required]
    public function setFlashBagInterface(RequestStack $session): void
    {
        $this->flashBag = $session->getSession()->getFlashBag();
    }

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
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
            if ($this->session->get('departement') !== null) {
                return $this->dptRepository->find($this->session->get('departement'));
            }

            return null;
        }

        if ($this->getUser() instanceof UserInterface && $this->getUser()->getDepartement() !== null) {
            return $this->getUser()?->getDepartement();
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
        $this->flashBag->add($type, $message);
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
