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
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Entity\Etudiant;
use App\Entity\Personnel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Umbrella\CoreBundle\Component\DataTable\DataTableFactory;
use Umbrella\CoreBundle\Component\DataTable\DTO\DataTable;
use Umbrella\CoreBundle\Component\Toast\Toast;

/**
 * Class BaseController.
 */
class BaseController extends AbstractController
{
    protected EntityManagerInterface $entityManager;
    protected TranslatorInterface $translator;
    protected FlashBagInterface $flashBag;
    private Departement $departement;

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

    public function getDepartement() : Departement
    {
        if ($this->getUser() !== null) {
            if (count($this->getUser()->getDepartements()) === 1) {
                $this->departement  = $this->getUser()->getDepartements()[0];
            } else {
                $this->departement = $this->getUser()->getPersonnelDepartements()[0];
            }
        }

        return $this->departement;
    }

    public function addFlashBag(string $type, string $message)
    {
        $this->flashBag->add($type, $message);
    }
}
