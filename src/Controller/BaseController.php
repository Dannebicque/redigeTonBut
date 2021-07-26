<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/BaseController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 24/05/2021 16:35
 */

namespace App\Controller;

use App\DTO\Secondaire;
use App\DTO\Tertiaire;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @required
     */
    public function setDepartementRepository(DepartementRepository $dptRepository): void
    {
        $this->dptRepository = $dptRepository;
    }

    /**
     * @required
     */
    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * @required
     */
    public function setFlashBagInterface(FlashBagInterface $flashBag): void
    {
        $this->flashBag = $flashBag;
    }

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function getCaracteristiques()
    {
        if ($this->getDepartement()->isTertiaire()) {
            return new Tertiaire();
        }

        if ($this->getDepartement()->isSecondaire()) {
            return new Secondaire();
        }
    }

    public function getDepartement(): Departement|RedirectResponse|null
    {
        if ($this->isGranted('ROLE_GT')) {
            if ($this->session->get('departement') !== null) {
                $this->departement = $this->dptRepository->find($this->session->get('departement'));
            } else {
                $this->departement = null;
            }
        } else {
            if ($this->getUser() !== null and $this->getUser()->getDepartement()!== null) {
                $this->departement = $this->getUser()->getDepartement();
            } else {
                return $this->redirectToRoute('app_login');//pas de département??
            }
        }

        return $this->departement;
    }

    public function addFlashBag(string $type, string $message)
    {
        $this->flashBag->add($type, $message);
    }
}
