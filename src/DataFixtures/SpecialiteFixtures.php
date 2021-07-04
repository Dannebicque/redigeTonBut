<?php

namespace App\DataFixtures;

use App\Entity\Annee;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SpecialiteFixtures extends Fixture
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $specialites = [
            [
                'sigle' => 'CJ',
                'libelle' => 'Carrière Juridique',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 2,
            ],
            [
                'sigle' => 'CS',
                'libelle' => 'Carrière Sociale',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE3,
                'n_annexe' => 3,
            ],
            [
                'sigle' => 'Chimie',
                'libelle' => 'Chimie',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 4,
            ],
            [
                'sigle' => 'GB',
                'libelle' => 'Génie Biologique',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 5,
            ],
            [
                'sigle' => 'GCCP',
                'libelle' => 'Génie chimique - Génie des procédés',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 6,
            ],
            [
                'sigle' => 'GCCD',
                'libelle' => 'Génie Civil - Construction Durable',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 7,
            ],
            [
                'sigle' => 'GEII',
                'libelle' => 'Génie électrique et informatique industrielle',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 8,
            ],
            [
                'sigle' => 'GIM',
                'libelle' => 'Génie industriel et maintenance',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 9,
            ],
            [
                'sigle' => 'GMP',
                'libelle' => 'Génie mécanique et productique',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 10,
            ],
            [
                'sigle' => 'GTE',
                'libelle' => 'Génie thermique et énergie',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 11,
            ],
            [
                'sigle' => 'GACO',
                'libelle' => 'Gestion administrative et commerciale des organisations',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 12,
            ],
            [
                'sigle' => 'GEA',
                'libelle' => 'Gestion des entreprises et administrations',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 13,
            ],
            [
                'sigle' => 'GLT',
                'libelle' => 'Gestion logistique et transport',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 14,
            ],
            [
                'sigle' => 'HSE',
                'libelle' => 'Hygiène, sécurité, environnement',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 15,
            ],
            [
                'sigle' => 'InfoCom',
                'libelle' => 'Information-Communication',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 16,
            ],
            [
                'sigle' => 'INFO',
                'libelle' => 'Informatique',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 17,
            ],
            [
                'sigle' => 'MP',
                'libelle' => 'Mesures physiques',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 18,
            ],
            [
                'sigle' => 'PEC',
                'libelle' => 'Packaging, Emballage et conditionnement',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 20,
            ],
            [
                'sigle' => 'QLIO',
                'libelle' => 'Qualité, logistique industrielle et organisation',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 21,
            ],
            [
                'sigle' => 'RT',
                'libelle' => 'Réseaux et télécommunications',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 22,
            ],
            [
                'sigle' => 'SGM',
                'libelle' => 'Sciences et génie des matériaux',
                'type' => 'secondaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 23,
            ],
            [
                'sigle' => 'STID',
                'libelle' => 'Statistique et informatique décisionnelle',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 24,
            ],
            [
                'sigle' => 'TC',
                'libelle' => 'Techniques de commercialisation',
                'type' => 'tertiaire',
                'typeStructure' => Departement::TYPE2,
                'n_annexe' => 25,
            ]
        ];

        $gt = new User();
        $gt->setCivilite('M.');
        $gt->setEmail('david.annebicque@gmail.com');
        $gt->setNom('GT');
        $gt->setPrenom('David');
        $gt->setIsVerified(true);
        $gt->setActif(true);
        $pass = $this->encoder->hashPassword($gt, 'test');
        $gt->setPassword($pass);
        $gt->setRoles(['ROLE_GT']);
        $manager->persist($gt);

        foreach ($specialites as $specialite) {
            $departement = new Departement();
            $departement->setSigle($specialite['sigle']);
            $departement->setLibelle($specialite['libelle']);
            $departement->setTypeDepartement($specialite['type']);
            $departement->setTypeStructure($specialite['typeStructure']);
            $departement->setNumeroAnnexe($specialite['n_annexe']);
            $manager->persist($departement);
            $manager->flush();
            $orLmd = 1;
            for ($i = 1; $i <= 3; $i++) {

                $annee = new Annee();
                $annee->setDepartement($departement);
                $annee->setLibelle('BUT' . $i);
                $annee->setOrdre($i);
                $annee->setLibelleLong('B.U.T. ' . $departement->getSigle() . ' ' . $i);
                $annee->setCodeEtape('BUT-' . $departement->getId() . '-' . $i);
                $manager->persist($annee);
                $manager->flush();
                for ($s = 1; $s <= 2; $s++) {
                    $semestre = new Semestre();
                    $semestre->setLibelle('S' . $orLmd);
                    $semestre->setAnnee($annee);
                    $semestre->setOrdreLmd($orLmd);
                    $semestre->setOrdreAnnee($i);
                    $manager->persist($semestre);
                    $orLmd++;
                }
                $manager->flush();
            }

        }

        $departement = new Departement();
        $departement->setSigle('MMI');
        $departement->setLibelle('Métiers du Multimédia et de l\'Internet');
        $departement->setTypeDepartement('secondaire');
        $departement->setTypeStructure(Departement::TYPE2);
        $departement->setNumeroAnnexe(19);
        $manager->persist($departement);
        $manager->flush();

        $pacd = new User();
        $pacd->setCivilite('M.');
        $pacd->setEmail('david.annebicque@univ-reims.fr');
        $pacd->setNom('Annebicque');
        $pacd->setPrenom('David');
        $pacd->setActif(true);
        $pacd->setIsVerified(true);
        $pass = $this->encoder->hashPassword($pacd, 'test');
        $pacd->setPassword($pass);
        $pacd->setDepartement($departement);
        $pacd->setRoles(['ROLE_PACD']);
        $manager->persist($pacd);

        $manager->flush();
    }
}
