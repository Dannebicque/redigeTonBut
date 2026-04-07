<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use App\Entity\Departement;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends BaseCrudController
{
    private const TEMP_PASSWORD_BYTES = 12;

    private array $roles = [];

    public function __construct(
        private readonly AdminUrlGenerator      $adminUrlGenerator,
        private readonly MailerInterface        $mailer,
        private readonly RequestStack           $requestStack,
        private readonly UserRepository         $userRepository,
        private readonly Security               $security,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->roles = [
                'Admin' => 'ROLE_ADMIN',
                'GT' => 'ROLE_GT',
                'IUT' => 'ROLE_IUT',
                'Labset' => 'ROLE_LABSET',
                'Editeur' => 'ROLE_EDITEUR',
                'Lecteur' => 'ROLE_LECTEUR',
                'Secrétaire de CPN (lecture/écriture)' => 'ROLE_CPN',
                'Membre CPN (lecture)' => 'ROLE_CPN_LECTEUR',
                'PACD' => 'ROLE_PACD',
            ];
        } elseif ($this->security->isGranted('ROLE_GT')) {
            $this->roles = [
                'GT' => 'ROLE_GT',
                'IUT' => 'ROLE_IUT',
                'Labset' => 'ROLE_LABSET',
                'Editeur' => 'ROLE_EDITEUR',
                'Lecteur' => 'ROLE_LECTEUR',
                'Secrétaire de CPN (lecture/écriture)' => 'ROLE_CPN',
                'Membre CPN (lecture)' => 'ROLE_CPN_LECTEUR',
                'PACD' => 'ROLE_PACD',
            ];
        } elseif ($this->security->isGranted('ROLE_CPN')) {
            $this->roles = [
                'IUT' => 'ROLE_IUT',
                'Editeur' => 'ROLE_EDITEUR',
                'Lecteur' => 'ROLE_LECTEUR',
                'PACD' => 'ROLE_PACD',
            ];
        } elseif ($this->security->isGranted('ROLE_PACD')) {
            $this->roles = [
                'IUT' => 'ROLE_IUT',
                'Editeur' => 'ROLE_EDITEUR',
                'Lecteur' => 'ROLE_LECTEUR',

            ];
        } else {
            $this->roles = [
                'IUT' => 'ROLE_IUT',
                'Lecteur' => 'ROLE_LECTEUR'
            ];
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $user = $this->security->getUser();

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_GT')) {
            // Pas de filtre, accès à tout
            return $qb;
        }

        if ($this->security->isGranted('ROLE_CPN') || $this->security->isGranted('ROLE_PACD')) {
            $departements = $user->getCpnDepartements();
            $departement = $user->getDepartement();
            // Regrouper les deux conditions dans un AND (...OR...) pour ne pas
            // polluer le WHERE global (notamment lors des recherches textuelles).
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('entity.departement', ':departements'),
                    $qb->expr()->eq('entity.departement', ':departement')
                )
            )
            ->setParameter('departements', $departements)
            ->setParameter('departement', $departement);
        }

        return $qb;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['nom' => 'ASC'])
            ->setSearchFields(['nom', 'prenom', 'email']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $resendPassword = Action::new('resendPassword', 'Renvoyer le mot de passe', 'fa fa-envelope')
            ->linkToCrudAction('resendPassword')
            ->setCssClass('text-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, $resendPassword)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::BATCH_DELETE) // Désactive l'action batch de suppression
            ->add(Crud::PAGE_DETAIL, $resendPassword)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, 'resendPassword', Action::DELETE]); // Ordre des actions

    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnIndex()->hideOnForm(),
            TextField::new('nom')->setLabel('Nom'),
            TextField::new('prenom')->setLabel('Prénom'),
            TextField::new('email')->setLabel('Email'),
            TextField::new('login')->setLabel('Login'),
            BooleanField::new('isVerified')->setLabel('Vérifié'),
            BooleanField::new('actif')->setLabel('Actif'),
            AssociationField::new('departement')->setLabel('Département')->setCrudController(DepartementCrudController::class), // Affiche le département
            ChoiceField::new('roles')
                ->setLabel('Rôles')
                ->setChoices($this->roles)
                ->allowMultipleChoices(true)
                ->setFormTypeOption('attr', [
                    'class' => 'roles-field'
                ]),
        ];


        $currentUser = $this->security->getUser();
        $cpnDepartementsField = AssociationField::new('CpnDepartements')
            ->setLabel('Départements CPN')
            ->setCrudController(DepartementCrudController::class)
            ->setFormTypeOption('multiple', true)
            ->setRequired(false);

        if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('ROLE_GT')) {
            // ROLE_CPN : restreindre aux départements de son propre périmètre CPN
            $allowedIds = array_map(
                fn(Departement $d) => $d->getId(),
                $currentUser->getCpnDepartements()->toArray()
            );

            $cpnDepartementsField->setQueryBuilder(
                fn(QueryBuilder $qb) => $qb
                    ->where('entity.id IN (:ids)')
                    ->setParameter('ids', $allowedIds ?: [0])
                    ->orderBy('entity.libelle', 'ASC')
            );
        }

        $fields[] = $cpnDepartementsField;


        return $fields;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        $newPassword = $this->assignTemporaryPassword($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);

        try {
            $this->sendPasswordEmail(
                $entityInstance,
                $newPassword,
                '[ORéBUT] Votre compte a été créé',
                'email/send_password.html.twig'
            );
            $this->addFlash('success', 'Compte créé et mot de passe envoyé par email.');
        } catch (\Throwable) {
            $this->addFlash('warning', 'Compte créé, mais l\'email d\'envoi du mot de passe a échoué.');
        }
    }

    #[AdminRoute(path: '/resend-password', name: 'resend_password')]
    public function resendPassword(AdminContext $context): Response
    {
        $user = $context->getEntity()->getInstance();

        if (!$user instanceof User) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToCurrentAdminPage();
        }

        $newPassword = $this->assignTemporaryPassword($user);
        $this->entityManager->flush();

        try {
            $this->sendPasswordEmail(
                $user,
                $newPassword,
                '[ORéBUT] Réinitialisation de votre mot de passe',
                'email/resend_password.html.twig'
            );
            $this->addFlash('success', 'Le mot de passe a été renvoyé avec succès.');
        } catch (\Throwable $e) {
            $this->addFlash('warning', 'Mot de passe mis à jour, mais l’email n’a pas pu être envoyé.');
        }

        return $this->redirectToCurrentAdminPage();
    }
//    public function resendPassword(): RedirectResponse
//    {
//        dump('ici');
//        $request = $this->requestStack->getCurrentRequest();
//        if (!$request || !$request->query->has('entityId')) {
//            $this->addFlash('danger', 'Aucun utilisateur sélectionné.');
//            return $this->redirectToCurrentAdminPage();
//        }
//
//        $userId = $request->query->get('entityId');
//        $user = $this->userRepository->find($userId);
//
//        if ($user) {
//            $newPassword = $this->assignTemporaryPassword($user);
//            $this->entityManager->flush();
//
//            try {
//                $this->sendPasswordEmail(
//                    $user,
//                    $newPassword,
//                    '[ORéBUT] Réinitialisation de votre mot de passe',
//                    'email/resend_password.html.twig'
//                );
//                $this->addFlash('success', 'Le mot de passe a été renvoyé avec succès.');
//            } catch (\Throwable) {
//                $this->addFlash('warning', 'Mot de passe mis à jour, mais l\'email n\'a pas pu être envoyé.');
//            }
//        } else {
//            $this->addFlash('danger', 'Utilisateur introuvable.');
//        }
//
//        return $this->redirectToCurrentAdminPage();
//    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('departement'))
            ->add(BooleanFilter::new('isVerified'))
            ->add(BooleanFilter::new('actif'))
            ->add(
                ArrayFilter::new('roles')
                    ->setChoices($this->roles)
            );
    }

    private function assignTemporaryPassword(User $user): string
    {
        $newPassword = $this->generateTemporaryPassword();
        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));

        return $newPassword;
    }

    private function generateTemporaryPassword(): string
    {
        try {
            return bin2hex(random_bytes(self::TEMP_PASSWORD_BYTES));
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Impossible de générer un mot de passe temporaire.', 0, $exception);
        }
    }

    private function sendPasswordEmail(User $user, string $newPassword, string $subject, string $template): void
    {
        $email = (new TemplatedEmail())
            ->from('orebut@iut.fr')
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'user' => $user,
                'newPassword' => $newPassword,
            ]);

        $this->mailer->send($email);
    }

    private function redirectToCurrentAdminPage(): RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request?->headers->get('referer');

        if (is_string($referer) && $referer !== '') {
            return $this->redirect($referer);
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
