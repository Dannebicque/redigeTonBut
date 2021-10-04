<?php


namespace App\Classes\Import;


use App\Entity\Departement;
use App\Entity\User;
use App\Event\UserEvent;
use App\Repository\DepartementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserImport
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private $departements;
    private $users = [];

    private UserPasswordHasherInterface $encoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        DepartementRepository $departementRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $encoder
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->encoder = $encoder;
        $departements = $departementRepository->findAll();

        foreach ($departements as $departement) {
            $this->departements[$departement->getSigle()] = $departement;
        }
        $users = $userRepository->findAll();
        foreach ($users as $user) {
            $this->users[] = $user->getEmail();
        }
    }

    public function import(?string $fichier)
    {
        //todo: refactoring
        //acces GT
        $excel = $this->openExcelFile($fichier);
        $sheet = $excel->getSheet(0);
        $ligne = 2;
        while (null !== $sheet->getCellByColumnAndRow(1, $ligne)->getValue()) {
            $email = trim($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
            if (!in_array($email, $this->users)) {
                $user = new User();
                $user->setNom(trim($sheet->getCellByColumnAndRow(1, $ligne)->getValue()));
                $user->setPrenom(trim($sheet->getCellByColumnAndRow(2, $ligne)->getValue()));
                $user->setCivilite(trim($sheet->getCellByColumnAndRow(3, $ligne)->getValue()));
                $user->setEmail($email);
                $user->setActif(true);
                $user->setIsVerified(true);
                $dep = trim($sheet->getCellByColumnAndRow(5, $ligne)->getValue());
                if ($dep !== null && $dep !== '') {
                    $user->setDepartement($this->departements[$dep]);
                }

                $role = strtoupper(trim($sheet->getCellByColumnAndRow(6, $ligne)->getValue()));
                if (!($role === 'ROLE_PACD' || $role === 'ROLE_EDITEUR' || $role === 'ROLE_LECTEUR' || $role === 'ROLE_GT' || $role === 'ROLE_CPN' || $role === 'ROLE_CPN_LECTEUR')) {
                    $role = 'ROLE_LECTEUR';
                }

                $user->setRoles([$role]);
                $password = mb_substr(md5(mt_rand()), 0, 10);
                $user->setPassword($this->encoder->hashPassword($user, $password));
                $this->entityManager->persist($user);
                $event = new UserEvent($user);
                $event->setPassword($password);
                $this->eventDispatcher->dispatch($event, UserEvent::CREATION_COMPTE);
            }
            $ligne++;
        }

        $this->entityManager->flush();

    }

    public function importDepartement(?string $fichier, Departement $departement)
    {
        $excel = $this->openExcelFile($fichier);
        $sheet = $excel->getSheet(0);
        $ligne = 2;
        while (null !== $sheet->getCellByColumnAndRow(1, $ligne)->getValue()) {
            $email = trim($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
            $dep = trim($sheet->getCellByColumnAndRow(5, $ligne)->getValue());
            if (!in_array($email, $this->users) && $dep !== null && $dep !== '' && $departement->getSigle() === $dep) {
                $user = new User();
                $user->setNom(trim($sheet->getCellByColumnAndRow(1, $ligne)->getValue()));
                $user->setPrenom(trim($sheet->getCellByColumnAndRow(2, $ligne)->getValue()));
                $user->setCivilite(trim($sheet->getCellByColumnAndRow(3, $ligne)->getValue()));
                $user->setEmail($email);
                $user->setActif(true);
                $user->setIsVerified(true);
                $user->setDepartement($departement);

                $role = strtoupper(trim($sheet->getCellByColumnAndRow(6, $ligne)->getValue()));
                if (!($role === 'ROLE_PACD' || $role === 'ROLE_EDITEUR' || $role === 'ROLE_LECTEUR')) {
                    $role = 'ROLE_LECTEUR';
                }

                $user->setRoles([$role]);
                $password = mb_substr(md5(mt_rand()), 0, 10);
                $user->setPassword($this->encoder->hashPassword($user, $password));
                $this->entityManager->persist($user);
                $event = new UserEvent($user);
                $event->setPassword($password);
                $this->eventDispatcher->dispatch($event, UserEvent::CREATION_COMPTE);
            }
            $ligne++;
        }

        $this->entityManager->flush();
    }

    private function openExcelFile($fichier)
    {
        $reader = new Xlsx();

        return $reader->load($fichier);
    }
}
