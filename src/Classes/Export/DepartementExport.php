<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Classes/Structure/DiplomeExport.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 31/05/2021 20:35
 */

namespace App\Classes\Export;

use App\Entity\Departement;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class DepartementExport
{
    private Environment $twig;

    private string $baseDir = '';

    public function __construct(
        KernelInterface $kernel,
        Environment $twig)
    {
        $this->twig = $twig;
        $this->baseDir = $kernel->getProjectDir();
    }

    public function exportRefentiel(Departement $departement, $format = 'xml'): Response
    {
        switch ($format) {
            case 'xml':
                return $this->exportFichierXml($departement);
            case 'json':
                return $this->exportFichierJson($departement);
        }

    }


    public function exportFichierJson(Departement $departement): Response
    {
        $tabJson = $this->genereJson($departement);
        $name = 'but-' . $departement->getSigle();

        $date = new DateTime('now');
        $name .= '-' . $date->format('dmY-His');
        $response = new Response(json_encode($tabJson));
        $response->headers->set('Content-type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.json"');

        return $response;
    }

    public function sauvegardeFichierJson(Departement $departement): Response
    {
        //sauvegarde en live du réferentiel de compétences
        $tabJson = $this->genereJson($departement);
        $name = 'but-' . $departement->getSigle();

        $date = new DateTime('now');
        $name .= '-' . $date->format('dmY-His');

        //création d'un fichier et sauvegarde dans le dossier /tmp
        $filePath = $this->baseDir.'/public/tmp/'.$departement->getNumeroAnnexe().'/' . $name . '.json';
        file_put_contents($filePath, json_encode($tabJson));
    }

    public function genereJson(Departement $departement): array
    {
        $data = [
            'specialite' => $departement->getSigle(),
            'specialite_long' => $departement->getLibelle(),
            'type' => 'B.U.T.',
            'annexe' => $departement->getNumeroAnnexe(),
            'type_structure' => $departement->getTypeStructure(),
            'type_departement' => $departement->getTypeDepartement(),
            'version' => $departement->getDateVersionCompetence() instanceof \DateTimeInterface
                ? $departement->getDateVersionCompetence()->format('Y-m-d H:i:s')
                : '-',
            'competences' => [],
            'parcours' => [],
        ];

        foreach ($departement->getApcCompetences() as $competence) {
            $competenceData = [
                'nom_court' => $competence->getNomCourt(),
                'numero' => $competence->getNumero(),
                'numero_identifiant' => $competence->getNumeroIdentifiant(),
                'libelle_long' => $competence->getLibelle(),
                'couleur' => $competence->getCouleur(),
                'id' => $competence->getCleUnique(),
                'situations' => [],
                'composantes_essentielles' => [],
                'niveaux' => [],
            ];

            foreach ($competence->getApcSituationProfessionnelles() as $situation) {
                $competenceData['situations'][] = $situation->getLibelle();
            }

            foreach ($competence->getApcComposanteEssentielles() as $composante) {
                $competenceData['composantes_essentielles'][] = $composante->getLibelle();
            }

            foreach ($competence->getApcNiveaux() as $niveau) {
                $niveauData = [
                    'ordre' => $niveau->getOrdre(),
                    'libelle' => $niveau->getLibelle(),
                    'annee' => $niveau->getAnnee() !== null ? 'BUT' . $niveau->getAnnee()->getOrdre() : 'BUT' . $niveau->getOrdre(),
                    'acs' => [],
                ];

                foreach ($niveau->getApcApprentissageCritiques() as $apprentissage) {
                    $niveauData['acs'][] = [
                        'code' => $apprentissage->getCode(),
                        'libelle' => $apprentissage->getLibelle(),
                    ];
                }

                $competenceData['niveaux'][] = $niveauData;
            }

            $data['competences'][] = $competenceData;
        }

        foreach ($departement->getApcParcours() as $parcour) {
            $parcourData = [
                'numero' => $parcour->getOrdre(),
                'libelle' => $parcour->getLibelle(),
                'code' => $parcour->getCode(),
                'annees' => [],
            ];

            for ($annee = 1; $annee <= 3; $annee++) {
                $anneeData = [
                    'ordre' => $annee,
                    'competences' => [],
                ];

                foreach ($parcour->getApcParcoursNiveaux() as $niveau) {
                    if (
                        (!$niveau->getNiveau()->getAnnee() && $niveau->getNiveau()->getOrdre() == $annee) ||
                        ($niveau->getNiveau()->getAnnee() && $niveau->getNiveau()->getAnnee()->getOrdre() == $annee)
                    ) {
                        $anneeData['competences'][] = [
                            'niveau' => $niveau->getNiveau()->getOrdre(),
                            'id' => $niveau->getNiveau()->getCompetence()->getCleUnique(),
                        ];
                    }
                }

                $parcourData['annees'][] = $anneeData;
            }

            $data['parcours'][] = $parcourData;
        }

        return $data;
    }


    public function exportFichierXml(Departement $departement): Response
    {
        $xmlContent = $this->twig->render('xml/export-referentiel-but.xml.twig', [
            'departement' => $departement,
            'competences' => $departement->getApcCompetences(),
            'parcours' => $departement->getApcParcours(),
        ]);
        $name = 'but-' . $departement->getSigle();

        $date = new DateTime('now');
        $name .= '-' . $date->format('dmY-His');
        $response = new Response($xmlContent);
        $response->headers->set('Content-type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.xml"');

        return $response;
    }

    public function exportProgramme(Departement $departement): Response
    {
        $xmlContent = $this->twig->render('xml/export-programme-but.xml.twig', [
            'semestres' => $departement->getSemestres(),
        ]);
        $name = 'but-pn-' . $departement->getSigle();


        return $this->exportFichier($xmlContent, $name);
    }

    public function compareJson(mixed $tabAncien, array $tabActuel)
    {
        // Je veux comparer les deux tableaux JSON, et créer un tableau de différences, avec pour chaque élément : si c'est un ajout, une suppression ou une modification. Chaque élément doit être identifié par son ID (à concaténer avec compétence, ac, ce, niveau... pour les différencier, et faciliter la récursion depuis l'affichage de la structure). La structure du json peut comporter plusieurs niveaux, donc il faut que la fonction puisse être récursive pour comparer les sous-éléments.
dump($tabAncien);
        $diff = [];

        //pour chaque item dans le tableau actuel, on vérifie s'il existe dans l'ancien tableau et on compare les valeurs, on va passer par un DTO pour faciliter la comparaison et éviter la duplication de code







        dd($diff);
        return $diff;






    }
}
