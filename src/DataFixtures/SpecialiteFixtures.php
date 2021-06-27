<?php

namespace App\DataFixtures;

use App\Entity\Departement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SpecialiteFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $specialites = [
             [
                'sigle' => 'CJ',
                'libelle' => 'Carrière Juridique',
                'type' => 'tertiaire',
                'n_annexe' => 2,
            ],
             [
                'sigle' => 'CS',
                'libelle' => 'Carrière Sociale',
                'type' => 'tertiaire',
                'n_annexe' => 3,
            ],
             [
                'sigle' => 'Chimie',
                'libelle' => 'Chimie',
                'type' => 'secondaire',
                'n_annexe' => 4,
            ],
            [
                'sigle' => 'GB',
                'libelle' => 'Génie Biologique',
                'type' => 'secondaire',
                'n_annexe' => 5,
            ],
            [
                'sigle' => 'GCCP',
                'libelle' => 'Génie chimique - Génie des procédés',
                'type' => 'secondaire',
                'n_annexe' => 6,
            ],
            [
                'sigle' => 'GCCD',
                'libelle' => 'Génie Civil - Construction Durable',
                'type' => 'secondaire',
                'n_annexe' => 7,
            ],
            [
                'sigle' => 'GEII',
                'libelle' => 'Génie électrique et informatique industrielle',
                'type' => 'secondaire',
                'n_annexe' => 8,
            ],
            [
                'sigle' => 'GIM',
                'libelle' => 'Génie industriel et maintenance',
                'type' => 'secondaire',
                'n_annexe' => 9,
            ],
            [
                'sigle' => 'GMP',
                'libelle' => 'Génie mécanique et productique',
                'type' => 'secondaire',
                'n_annexe' => 10,
            ],
            [
                'sigle' => 'GTE',
                'libelle' => 'Génie thermique et énergie',
                'type' => 'secondaire',
                'n_annexe' => 11,
            ],
            [
                'sigle' => 'GACO',
                'libelle' => 'Gestion administrative et commerciale des organisations',
                'type' => 'tertiaire',
                'n_annexe' => 12,
            ],
            [
                'sigle' => 'GEA',
                'libelle' => 'Gestion des entreprises et administrations',
                'type' => 'tertiaire',
                'n_annexe' => 13,
            ],
            [
                'sigle' => 'GLT',
                'libelle' => 'Gestion logistique et transport',
                'type' => 'tertiaire',
                'n_annexe' => 14,
            ],
            [
                'sigle' => 'HSE',
                'libelle' => 'Hygiène, sécurité, environnement',
                'type' => 'secondaire',
                'n_annexe' => 15,
            ],
            [
                'sigle' => 'InfoCom',
                'libelle' => 'Information-Communication',
                'type' => 'tertiaire',
                'n_annexe' => 16,
            ],
            [
                'sigle' => 'INFO',
                'libelle' => 'Informatique',
                'type' => 'secondaire',
                'n_annexe' => 17,
            ],
            [
                'sigle' => 'MP',
                'libelle' => 'Mesures physiques',
                'type' => 'secondaire',
                'n_annexe' => 18,
            ],
            [
                'sigle' => 'MMI',
                'libelle' => 'Métiers du Multimédia et de l\'Internet',
                'type' => 'secondaire',
                'n_annexe' => 19,
            ],
            [
                'sigle' => 'PEC',
                'libelle' => 'Packaging, Emballage et conditionnement',
                'type' => 'secondaire',
                'n_annexe' => 20,
            ],
            [
                'sigle' => 'QLIO',
                'libelle' => 'Qualité, logistique industrielle et organisation',
                'type' => 'secondaire',
                'n_annexe' => 21,
            ],
            [
                'sigle' => 'RT',
                'libelle' => 'Réseaux et télécommunications',
                'type' => 'secondaire',
                'n_annexe' => 22,
            ],
            [
                'sigle' => 'SGM',
                'libelle' => 'Sciences et génie des matériaux',
                'type' => 'secondaire',
                'n_annexe' => 23,
            ],
            [
                'sigle' => 'STID',
                'libelle' => 'Statistique et informatique décisionnelle',
                'type' => 'tertiaire',
                'n_annexe' => 24,
            ],
            [
                'sigle' => 'TC',
                'libelle' => 'Techniques de commercialisation',
                'type' => 'tertiaire',
                'n_annexe' => 25,
            ]
        ];

        foreach ($specialites as $specialite) {
            $departement = new Departement();
            $departement->setSigle($specialite['sigle']);
            $departement->setLibelle($specialite['libelle']);
            $departement->setTypeDepartement($specialite['type']);
            $manager->persist($departement);
        }

        $manager->flush();
    }
}
