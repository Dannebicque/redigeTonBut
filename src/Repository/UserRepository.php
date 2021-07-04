<?php

namespace App\Repository;

use App\Entity\Departement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findPacd(Departement $departement)
    {
        return $this->findOneByDepartementAndRole($departement, 'ROLE_PACD');
    }

    public function findCpn(Departement $departement)
    {
        return $this->findOneByDepartementAndRole($departement, 'ROLE_CPN');
    }

    private function findOneByDepartementAndRole(Departement $departement, string $role)
    {
        return $this->createQueryBuilder('u')
            ->where('u.departement = :departement')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('departement', $departement->getId())
            ->setParameter('role', $role)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('u')
            ->where('u.departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->getQuery()
            ->getResult();
    }
}
