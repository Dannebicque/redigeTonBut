<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Classes/Structure/DiplomeExport.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 31/05/2021 20:35
 */

namespace App\Classes\Export;

use App\DTO\PreconisationSemestre;
use App\Entity\ApcCompetence;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\ApcSaeRessource;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\Version;
use App\Repository\AnneeRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcSaeParcoursRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class DepartementExport
{
    private Environment $twig;

    private string $baseDir = '';

    public function __construct(
        private ApcSaeParcoursRepository       $apcSaeParcoursRepository,
        private ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        private ApcParcoursNiveauRepository    $apcParcoursNiveauRepository,
        KernelInterface                        $kernel,
        Environment                            $twig,
        private readonly AnneeRepository $anneeRepository)
    {
        $this->twig = $twig;
        $this->baseDir = $kernel->getProjectDir();
    }

    public function exportRefentiel(Version $version, $format = 'xml'): Response
    {
        switch ($format) {
            case 'xml':
                return $this->exportFichierXml($version);
            case 'json':
                return $this->exportFichierJson($version);
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
            'description' => $departement->getTextePresentation(),
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
                'description' => $parcour->getTextePresentation(),
                'annees' => [],
            ];

            for ($annee = 1; $annee <= 3; $annee++) {
                $anneeData = [
                    'ordre' => $annee,
                    'competences' => [],
                ];

                foreach ($parcour->getApcParcoursNiveaux() as $niveau) {
                    if (
                        (!$niveau->getNiveau()?->getAnnee() && $niveau->getNiveau()?->getOrdre() == $annee) ||
                        ($niveau->getNiveau()?->getAnnee() && $niveau->getNiveau()?->getAnnee()?->getOrdre() == $annee)
                    ) {
                        $anneeData['competences'][] = [
                            'niveau' => $niveau->getNiveau()?->getOrdre(),
                            'id' => $niveau->getNiveau()?->getCompetence()?->getCleUnique(),
                        ];
                    }
                }

                $parcourData['annees'][] = $anneeData;
            }

            $data['parcours'][] = $parcourData;
        }

        return $data;
    }


    public function exportFichierXml(Version $version): Response
    {
        $departement = $version->getDepartement();
        $xmlContent = $this->twig->render('xml/export-referentiel-but.xml.twig', [
            'departement' => $departement,
            'competences' => $version->getApcCompetences(),
            'parcours' => $version->getApcParcours(),
        ]);
        $name = 'but-' . $departement->getSigle();

        $date = new DateTime('now');
        $name .= '-' . $date->format('dmY-His');
        $response = new Response($xmlContent);
        $response->headers->set('Content-type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.xml"');

        return $response;
    }

    public function exportProgramme(Version $version): Response
    {
        $departement = $version->getDepartement();
        $xmlContent = $this->twig->render('xml/export-programme-but.xml.twig', [
            'semestres' => $version->getSemestres(),
        ]);
        $name = 'but-pn-' . $departement->getSigle();


        return $this->exportFichier($xmlContent, $name);
    }

    public function genereJsonReferentiel(Version $version)
    {
        $departement = $version->getDepartement();
        $tabJson = [];
        $tabJson['specialite'] = $departement->getSigle();
        $tabJson['specialite_long'] = $departement->getLibelle();
        $tabJson['type'] = 'B.U.T.';
        $tabJson['description'] = $version->getTextePresentation();
        $tabJson['annexe'] = $departement->getNumeroAnnexe();
        $tabJson['type_structure'] = $departement->getTypeStructure();
        $tabJson['alt_but_1'] = $version->getAltBut1();
        $tabJson['alt_but_2'] = $version->getAltBut2();
        $tabJson['alt_but_3'] = $version->getAltBut3();

        foreach ($version->getApcParcours() as $apcParcour) {
            $tabJson['parcours'][$apcParcour->getNumeroIdentifiant()] = [
                'numero' => $apcParcour->getOrdre(),
                'libelle' => $apcParcour->getLibelle(),
                'code' => $apcParcour->getCode(),
                'description' => $apcParcour->getTextePresentation(),
                'modalites_particulieres' => $apcParcour->getModalitesParticulieres(),
            ];

            $tabSemestres = [];
            if ($departement->gettypeStructure() !== 'type3') {
                // on récupère les semestre de l'année 1 puis ceux du parcours
                $annees = $version->getAnnees();
                foreach ($annees as $annee) {
                        $sems = $annee->getSemestres();
                        foreach ($sems as $sem) {
                            $tabSemestres[$sem->getOrdreLmd()] = $sem;
                    }
                }


            }

            /** @var Semestre $semestre */
            foreach ($tabSemestres as $semestre) {

                if ($departement->gettypeStructure() !== 'type3' and ($semestre->getOrdreLmd() < 3 )) {
                    $ressources = $semestre->getApcRessources();
                    $ressourcesAl = [];
                    $saes = $semestre->getApcSaes();
                    $saesAl = [];
                } else {
                    $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $apcParcour);
                    $ressourcesAl = $this->apcRessourceParcoursRepository->findBySemestreAl($semestre, $apcParcour);
                    $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $apcParcour);
                    $saesAl = $this->apcSaeParcoursRepository->findBySemestreAl($semestre, $apcParcour);
                }
                $tabSem = [];

                foreach ($ressources as $ressource) {
                    $tabSem['ressources'][] = $this->convertRessourceToJson($ressource);
                }

                foreach ($ressourcesAl as $ressource) {
                    $tabSem['ressourcesAl'][] = $this->convertRessourceToJson($ressource);
                }

                foreach ($saes as $sae) {
                    $tabSem['saes'][] = $this->convertSaeToJson($sae);
                }

                foreach ($saesAl as $sae) {
                    $tabSem['saesAl'][] = $this->convertSaeToJson($sae);
                }


                $tabJson['parcours'][$apcParcour->getNumeroIdentifiant()]['semestres'] [$semestre->getOrdreLmd()] = $tabSem;
            }
        }

        return $tabJson;
    }

    private function convertRessourceToJson(ApcRessource $ressource): array
    {
        $res= [
            'id' => $ressource->getId(),
            'libelle' => $ressource->getLibelle(),
            'code' => $ressource->getCodeMatiere(),
            'mots_cles' => $ressource->getMotsCles(),
            'description' => $ressource->getDescription(),
            'ordre' => $ressource->getOrdre(),
            'ficheAdaptationLocale' => $ressource->getFicheAdaptationLocale(),
            'cmPreco' => $ressource->getCmPreco(),
            'tdPreco' => $ressource->getTdPreco(),
            'tpPreco' => $ressource->getTpPreco(),
        ];

        $preRequis = [];
        //prerequis
        foreach ($ressource->getRessourcesPreRequises() as $preR) {
            $preRequis[] = [
                'id' => $preR->getId(),
                'libelle' => $preR->getLibelle(),
                'code' => $preR->getCodeMatiere(),
            ];
        }
        $res['preRequis'] = $preRequis;

        //competences
        $comps = [];
        /** @var ApcCompetence $comp */
        foreach ($ressource->getCompetences() as $comp) {
            $comps[] = [
                'id' => $comp->getId(),
                'libelle' => $comp->getLibelle(),
                'nomCourt' => $comp->getNomCourt(),
                'code' => $comp->getCleUnique(),
            ];
        }
        $res['competences'] = $comps;

        // Apprentissages critiques
        $acs = [];
        foreach ($ressource->getApcRessourceApprentissageCritiques() as $ac) {
            $acs[] = [
                'id_liaison' => $ac->getId(),
                'id_ac' => $ac->getApprentissageCritique()?->getId(),
                'code' => $ac->getApprentissageCritique()?->getCode(),
                'libelle' => $ac->getApprentissageCritique()?->getLibelle(),
            ];
        }
        $res['acs'] = $acs;

        // SAES associées
        $saes = [];
        /** @var ApcSaeRessource $sae */
        foreach ($ressource->getApcSaeRessources() as $sae) {
            $saes[] = [
                'id_liaison' => $sae->getId(),
                'id' => $sae->getSae()?->getId(),
                'libelle' => $sae->getSae()?->getLibelle(),
                'code' => $sae->getSae()?->getCodeMatiere(),
            ];
        }
        $res['saes'] = $saes;

        return $res;
    }

    private function convertSaeToJson(ApcSae $sae): array
    {
        $sa= [
            'id' => $sae->getId(),
            'libelle' => $sae->getLibelle(),
            'code' => $sae->getCodeMatiere(),
            'exemples' => $sae->getExemples(),
            'objectifs' => $sae->getObjectifs(),
            'ordre' => $sae->getOrdre(),
            'ficheAdaptationLocale' => $sae->getFicheAdaptationLocale(),
            'portfolio' => $sae->getPortfolio(),
            'stage' => $sae->getStage(),
            'projetPpn' => $sae->getProjetPpn(),
        ];

        //competences
        $comps = [];
        /** @var ApcCompetence $comp */
        foreach ($sae->getCompetences() as $comp) {
            $comps[] = [
                'id' => $comp->getId(),
                'libelle' => $comp->getLibelle(),
                'nomCourt' => $comp->getNomCourt(),
                'code' => $comp->getCleUnique(),
            ];
        }
        $sa['competences'] = $comps;

        // Apprentissages critiques
        $acs = [];
        foreach ($sae->getApcSaeApprentissageCritiques() as $ac) {
            $acs[] = [
                'id_liaison' => $ac->getId(),
                'id_ac' => $ac->getApprentissageCritique()?->getId(),
                'code' => $ac->getApprentissageCritique()?->getCode(),
                'libelle' => $ac->getApprentissageCritique()?->getLibelle(),
            ];
        }
        $sa['acs'] = $acs;

        // SAES associées
        $saes = [];
        foreach ($sae->getApcSaeRessources() as $res) {
            $saes[] = [
                'id_liaison' => $res->getId(),
                'id' => $res->getRessource()?->getId(),
                'libelle' => $res->getRessource()?->getLibelle(),
                'code' => $res->getRessource()?->getCodeMatiere(),
            ];
        }
        $sa['saes'] = $saes;


        return $sa;
    }
}
