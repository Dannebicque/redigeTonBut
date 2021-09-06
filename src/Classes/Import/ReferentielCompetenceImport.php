<?php


namespace App\Classes\Import;


use App\Entity\Annee;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\ApcParcoursNiveau;
use App\Entity\ApcRessource;
use App\Entity\ApcRessourceApprentissageCritique;
use App\Entity\ApcRessourceCompetence;
use App\Entity\ApcRessourceParcours;
use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\ApcSaeCompetence;
use App\Entity\ApcSaeParcours;
use App\Entity\ApcSaeRessource;
use App\Entity\ApcSituationProfessionnelle;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Utils\Codification;
use App\Utils\Convert;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ReferentielCompetenceImport
{
    private string $fichier;
    private Departement $departement;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function import(Departement $departement, string $fichier, $type)
    {
        $this->fichier = $fichier;
        $this->departement = $departement;
        $ext = pathinfo($this->fichier, PATHINFO_EXTENSION);

        //supprimer les anciens référentiels.
        //vérifier si un référentiel de compétence est présent

        if ($ext === 'xml') {
            switch ($type) {
                case 'competences':
                    $this->importCompetence();
                    break;
                case 'formation':
                    $this->importFormation();
                    break;
            }
        } elseif ($ext === 'xlsx' || $ext === 'xls') {
            switch ($type) {
                case 'competences':
                    $this->importCompetenceExcel();
                    break;
                case 'formation':
                    $this->importFormationExcel();
                    break;
            }
        }
    }

    private function importCompetenceExcel()
    {
        $annees = $this->entityManager->getRepository(Annee::class)->findByDepartement($this->departement);
        $tAnnees = [];
        foreach ($annees as $annee) {
            $tAnnees['BUT' . $annee->getOrdre()] = $annee;
        }

        $excel = $this->openExcelFile();
        $sheet = $excel->getSheet(0);

        $ligne = 1;
        $tCompetences = [];
        $lastComp = null;//compétence en cours
        $lastNiv = null;//niveau en cours
        $numCompEssentiel = 1;
        while (null !== $sheet->getCellByColumnAndRow(2, $ligne)->getValue()) {
            switch ($sheet->getCellByColumnAndRow(2, $ligne)->getValue()) {
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                    //nouvelle compétence
                    $comp = new ApcCompetence($this->departement);
                    $comp->setCouleur('c' . $sheet->getCellByColumnAndRow(2, $ligne)->getValue());
                    $comp->setNumero($sheet->getCellByColumnAndRow(2, $ligne)->getValue());
                    $comp->setLibelle($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
                    $comp->setNomCourt($sheet->getCellByColumnAndRow(3, $ligne)->getValue());
                    $this->entityManager->persist($comp);
                    $tCompetences[trim($comp->getNomCourt())] = [];
                    $lastComp = $comp;
                    $numCompEssentiel = 1;
                    break;
                case 'Composantes essentielles':
                    $compos = new ApcComposanteEssentielle();
                    $compos->setLibelle($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
                    $compos->setCompetence($lastComp);
                    $compos->setOrdre($numCompEssentiel);
                    $this->entityManager->persist($compos);
                    $numCompEssentiel++;
                    break;
                case 'Situations professionnelles':
                    $sit = new ApcSituationProfessionnelle();
                    $sit->setLibelle($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
                    $sit->setCompetence($lastComp);
                    $this->entityManager->persist($sit);
                    break;
                case 'Niveaux':
                    $niv = new ApcNiveau();
                    $cle = $sheet->getCellByColumnAndRow(3, $ligne)->getValue();
                    $ordre = substr($cle, 3, 1);
                    if (array_key_exists($cle, $tAnnees)) {
                        $niv->setAnnee($tAnnees[$cle]);
                    }

                    $niv->setLibelle($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
                    $niv->setOrdre($ordre);
                    $niv->setCompetence($lastComp);
                    $tCompetences[trim($lastComp->getNomCourt())][$niv->getOrdre()] = $niv;
                    $lastNiv = $niv;
                    $ordreAc = 1;
                    $this->entityManager->persist($niv);
                    break;
                case 'Apprentissages critiques':
                    $app = new ApcApprentissageCritique();
                    $app->setLibelle($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
                    $app->setOrdre($ordreAc);
                    $app->setNiveau($lastNiv);
                    $app->setCode(Codification::codeApprentissageCritique($app));
                    $ordreAc++;
                    $this->entityManager->persist($app);
                    break;
            }
            $ligne++;
        }
        $sheet = $excel->getSheet(1);

        $tParcour = [];
        $ligne = 1;
        while (null !== $sheet->getCellByColumnAndRow(1, $ligne)->getValue()) {
            if ($sheet->getCellByColumnAndRow(5, $ligne)->getValue() === 'BUT1') {
                //nouveau parcours
                $parc = new ApcParcours($this->departement);
                $ordre = $sheet->getCellByColumnAndRow(2, $ligne)->getValue();
                $parc->setCode($sheet->getCellByColumnAndRow(4, $ligne)->getValue());
                $parc->setLibelle($sheet->getCellByColumnAndRow(3, $ligne)->getValue());
                $parc->setCouleur('p' . $ordre);
                $this->entityManager->persist($parc);
                $tParcour[$ordre] = $parc;
            } else {
                //décomposition du parcours
                for ($i = 5; $i <= 7; $i++) {
                    if (trim($sheet->getCellByColumnAndRow($i, $ligne)->getValue()) !== '') {
                        $pn = new ApcParcoursNiveau();
                        $nom = $sheet->getCellByColumnAndRow($i, $ligne)->getValue();
                        $iParc = $sheet->getCellByColumnAndRow(2, $ligne)->getValue();
                        $pn->setNiveau($tCompetences[trim($nom)][$i - 4]);
                        $pn->setParcours($tParcour[$iParc]);
                        $this->entityManager->persist($pn);
                    }
                }

            }
            $ligne++;
        }
        $this->entityManager->flush();
    }

    private function importCompetence()
    {
        $annees = $this->entityManager->getRepository(Annee::class)->findByDepartement($this->departement);
        $tAnnees = [];
        foreach ($annees as $annee) {
            $tAnnees['BUT' . $annee->getOrdre()] = $annee;
        }

        $xml = $this->openXmlFile();
        $tCompetences = [];
        foreach ($xml->competences->competence as $competence) {
            $comp = new ApcCompetence($this->departement);
            $comp->setCouleur($competence['couleur']);
            $comp->setNumero(substr($competence['couleur'], 1, 1));
            $comp->setLibelle($competence['libelle_long']);
            $comp->setNomCourt($competence['name']);
            $tCompetences[$comp->getNomCourt()] = [];

            $this->entityManager->persist($comp);

            foreach ($competence->situations->situation as $situation) {
                $sit = new ApcSituationProfessionnelle();
                $sit->setLibelle($situation);
                $sit->setCompetence($comp);
                $this->entityManager->persist($sit);
            }
            $or = 1;
            foreach ($competence->composantes_essentielles->composante as $composante) {
                $compos = new ApcComposanteEssentielle();
                $compos->setLibelle($composante);
                $compos->setCompetence($comp);
                $compos->setOrdre($or);
                $this->entityManager->persist($compos);
                $or++;
            }

            foreach ($competence->niveaux->niveau as $niveau) {
                $niv = new ApcNiveau();

                if (array_key_exists('annee', (array)$niveau->attributes()) && array_key_exists($niveau['annee'],
                        $tAnnees)) {
                    $niv->setAnnee($tAnnees[$niveau['annee']]);
                } else {
                    $cle = 'BUT' . $niveau['ordre'];
                    if (array_key_exists($cle, $tAnnees)) {
                        $niv->setAnnee($tAnnees[$cle]);
                    }
                }
                $niv->setLibelle($niveau['libelle']);
                $niv->setOrdre((int)$niveau['ordre']);
                $niv->setCompetence($comp);
                $tCompetences[$comp->getNomCourt()][$niv->getOrdre()] = $niv;

                $this->entityManager->persist($niv);

                foreach ($niveau->acs->ac as $ac) {
                    $app = new ApcApprentissageCritique();
                    $app->setLibelle($ac[0]);
                    $app->setOrdre(substr($ac['code'], 4, 2));
                    $app->setNiveau($niv);
                    $app->setCode(Codification::codeApprentissageCritique($app));
                    $this->entityManager->persist($app);
                }
            }
        }
        $i = 1;
        foreach ($xml->parcours->parcour as $parcour) {
            $parc = new ApcParcours($this->departement);
            $parc->setCode($parcour['code']);
            $parc->setLibelle($parcour['libelle']);
            $parc->setCouleur('p' . $i);
            $i++;
            $this->entityManager->persist($parc);
            foreach ($parcour->annee as $annee) {
                foreach ($annee->competence as $parcNiveau) {
                    $pn = new ApcParcoursNiveau();
                    $pn->setNiveau($tCompetences[trim((string)$parcNiveau['nom'])][trim((string)$parcNiveau['niveau'])]);
                    $pn->setParcours($parc);
                    $this->entityManager->persist($pn);
                }
            }
        }
        $this->entityManager->flush();

    }

    private function openXmlFile()
    {
        if (file_exists($this->fichier)) {
            return simplexml_load_string(file_get_contents($this->fichier));
        }

        throw new FileNotFoundException();
    }

    private function importFormation()
    {
        $xml = $this->openXmlFile();
        $tAcs = $this->entityManager->getRepository(ApcApprentissageCritique::class)->findOneByDepartementArray($this->departement);
        $tParcours = $this->entityManager->getRepository(ApcParcours::class)->findOneByDepartementArray($this->departement);
        $tCompetences = $this->entityManager->getRepository(ApcCompetence::class)->findOneByDepartementArray($this->departement);
        $tSem = [];
        foreach ($xml->semestre as $sem) {

            $semestre = $this->entityManager->getRepository(Semestre::class)->findOneByDepartementEtNumero($this->departement,
                $sem['numero'], $sem['ordreAnnee']);

            if (null !== $semestre) {
                $tSem[] = $semestre;
                $tRessources = [];
                $tabPrerequis = [];
                foreach ($sem->ressources->ressource as $ressource) {
                    $ar = new ApcRessource();
                    $ar->setSemestre($semestre);
                    $ar->setOrdre((int)$ressource['ordre']);
                    $ar->setLibelle($ressource->titre);
                    $ar->setHeuresTotales($ressource['heuresCMTD']);
                    $ar->setTpPpn($ressource['heuresTP']);
                    $ar->setDescription((string)$ressource->description);
                    $ar->setMotsCles((string)$ressource->motsCles);
                    $ar->setCodeMatiere((string)$ressource['code']);
                    //$ar->setCodeMatiere(Codification::codeRessource($ar)); -- todo: renommer le semestre à posteriori
                    $this->entityManager->persist($ar);
                    $tRessources[$ar->getCodeMatiere()] = $ar;

                    //acs
                    if ($ressource->acs !== null) {
                        foreach ($ressource->acs->ac as $ac) {
                            if (array_key_exists(trim((string)$ac), $tAcs)) {
                                $rac = new ApcRessourceApprentissageCritique($ar, $tAcs[trim((string)$ac)]);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }

                    //competences
                    if ($ressource->competences !== null) {
                        foreach ($ressource->competences->competence as $comp) {
                            if (array_key_exists(trim((string)$comp['nom']), $tCompetences)) {
                                $rac = new ApcRessourceCompetence($ar, $tCompetences[trim((string)$comp['nom'])]);
                                $rac->setCoefficient((float)$comp['coefficient']);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }

                    //prerequis
                    if ($ressource->prerequis !== null && $ressource->prerequis->ressource !== null) {
                        foreach ($ressource->prerequis->ressource as $r) {
                            if (!array_key_exists($ar->getCodeMatiere(), $tabPrerequis)) {
                                $tabPrerequis[$ar->getCodeMatiere()] = [];
                            }
                            $tabPrerequis[$ar->getCodeMatiere()][] = (string)$r; //on sauvegarde et on trairera à la fin des ressources;
                        }
                    }

                    //parcours
                    if ($ressource->listeParcours !== null && $ressource->listeParcours->parcours !== null) {
                        foreach ($ressource->listeParcours->parcours as $parcours) {
                            if (array_key_exists(trim((string)$parcours), $tParcours)) {
                                $rac = new ApcRessourceParcours($ar, $tParcours[trim((string)$parcours)]);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }
                    //les saes seront ajoutée par les SAE
                }

                foreach ($sem->saes->sae as $sae) {
                    $ar = new ApcSae();
                    $ar->setSemestre($semestre);
                    $ar->setLibelle($sae->titre);
                    $ar->setOrdre((int)$sae['ordre']);
                    $ar->setDescription((string)$sae->description);
                    $ar->setObjectifs((string)$sae->objectifs);
                    $ar->setCodeMatiere((string)$sae['code']);
                    //$ar->setCodeMatiere(Codification::codeSae($ar));

                    $this->entityManager->persist($ar);

                    //acs
                    if ($sae->acs !== null) {
                        foreach ($sae->acs->ac as $ac) {
                            if (array_key_exists(trim((string)$ac), $tAcs)) {
                                $rac = new ApcSaeApprentissageCritique($ar, $tAcs[trim((string)$ac)]);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }

                    //competences
                    if ($sae->competences !== null) {
                        foreach ($sae->competences->competence as $comp) {
                            if (array_key_exists(trim((string)$comp['nom']), $tCompetences)) {
                                $rac = new ApcSaeCompetence($ar, $tCompetences[trim((string)$comp['nom'])]);
                                $rac->setCoefficient((float)$comp['coefficient']);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }
                    //Ressources
                    if ($sae->ressources !== null) {
                        foreach ($sae->ressources->ressource as $comp) {
                            if (array_key_exists(trim((string)$comp), $tRessources)) {
                                $rac = new ApcSaeRessource($ar, $tRessources[trim((string)$comp)]);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }

                    //Parcours
                    if ($sae->listeParcours !== null && $sae->listeParcours->parcours) {
                        foreach ($sae->listeParcours->parcours as $parcours) {
                            if (array_key_exists(trim((string)$parcours), $tParcours)) {
                                $rac = new ApcSaeParcours($ar, $tParcours[trim((string)$parcours)]);
                                $this->entityManager->persist($rac);
                            }
                        }
                    }
                }
                foreach ($tabPrerequis as $key => $tpr) {
                    foreach ($tpr as $r) {
                        if (array_key_exists($r, $tRessources) && array_key_exists($key, $tRessources)) {
                            $tRessources[$key]->addRessourcesPreRequise($tRessources[$r]);
                            $tRessources[$r]->addApcRessource($tRessources[$key]);
                        }
                    }
                }
            }
        }

        foreach ($tSem as $sem) {
            foreach ($sem->getApcRessources() as $ressource) {
                $ressource->setCodeMatiere(Codification::codeRessource($ressource));
            }

            foreach ($sem->getApcSaes() as $sae) {
                $sae->setCodeMatiere(Codification::codeSae($sae));
            }
        }

        $this->entityManager->flush();
    }

    private function openExcelFile()
    {
        $reader = new Xlsx();

        return $reader->load($this->fichier);
    }

    private function importFormationExcel()
    {
        $tSemestres = $this->entityManager->getRepository(Semestre::class)->findByDepartementArray($this->departement);
        $tCompetences = $this->entityManager->getRepository(ApcCompetence::class)->findByDepartementArray($this->departement);
        $tAcs = $this->entityManager->getRepository(ApcApprentissageCritique::class)->findOneByDepartementArray($this->departement);
        $tSaes = $this->entityManager->getRepository(ApcSae::class)->findByDepartementArray($this->departement);
        $tabParcours = $this->entityManager->getRepository(ApcParcours::class)->findOneByDepartementArray($this->departement);

        $excel = $this->openExcelFile();
        $sheet = $excel->getSheet(0);//ressources
        $tabRessources = [];

        $ligne = 2;
        while (null !== $sheet->getCellByColumnAndRow(1, $ligne)->getValue()) {
            echo $ligne;
            $res = new ApcRessource();
            $res->setSemestre($tSemestres[trim($sheet->getCellByColumnAndRow(1, $ligne)->getValue())]);
            $res->setCodeMatiere(trim($sheet->getCellByColumnAndRow(2, $ligne)->getValue()));
            $res->setLibelle(trim($sheet->getCellByColumnAndRow(3, $ligne)->getValue()));
            $res->setLibelleCourt(trim($sheet->getCellByColumnAndRow(4, $ligne)->getValue()));
            $res->setOrdre(trim($sheet->getCellByColumnAndRow(5, $ligne)->getValue()));
            //compétences
            for ($i = 1; $i <= 6; $i++) {
                if ($sheet->getCellByColumnAndRow(5 + $i, $ligne)->getValue() !== null) {
                    //compétence
                    $acResComp = new ApcRessourceCompetence($res, $tCompetences['c'.$i]);
                    $this->entityManager->persist($acResComp);

                    $acs = explode(';', $sheet->getCellByColumnAndRow(5 + $i, $ligne)->getValue());
                    //ajout des AC
                    foreach ($acs as $codeAc) {
                        $codeAc = trim($codeAc);
                        if (array_key_exists($codeAc, $tAcs)) {
                            $acRes = new ApcRessourceApprentissageCritique($res, $tAcs[$codeAc]);
                            $this->entityManager->persist($acRes);
                        }
                    }
                }
            }

            //SAE (12)
            if ($sheet->getCellByColumnAndRow(12, $ligne)->getValue() !== null) {
                $saes = explode(';', $sheet->getCellByColumnAndRow(12, $ligne)->getValue());
                //ajout des AC
                foreach ($saes as $sae) {
                    $sae = trim($sae);
                    if (array_key_exists($sae, $tSaes)) {
                        $resSae = new ApcSaeRessource($tSaes[$sae], $res);
                        $this->entityManager->persist($resSae);
                    }
                }
            }
            //ressources (13)
            if ($sheet->getCellByColumnAndRow(13, $ligne)->getValue() !== null) {
                $ressources = explode(';', $sheet->getCellByColumnAndRow(13, $ligne)->getValue());
                //ajout des AC
                foreach ($ressources as $ressource) {
                    $ressource = trim($ressource);
                    if (array_key_exists($ressource, $tabRessources)) {
                        $res->addRessourcesPreRequise($tabRessources[$ressource]);
                        $tabRessources[$ressource]->addApcRessource($res);
                    }
                }
            }

            $res->setHeuresTotales(Convert::convertToFloat($sheet->getCellByColumnAndRow(14,
                $ligne)->getValue()));//a convertir
            $res->setTpPpn(Convert::convertToFloat($sheet->getCellByColumnAndRow(15, $ligne)->getValue()));//a convertir
            $res->setDescription(trim($sheet->getCellByColumnAndRow(16, $ligne)->getValue()));
            $res->setMotsCles(trim($sheet->getCellByColumnAndRow(17, $ligne)->getValue()));
            //parcours
            if ($sheet->getCellByColumnAndRow(18, $ligne)->getValue() !== null) {
                $parcours = explode(';', $sheet->getCellByColumnAndRow(18, $ligne)->getValue());
                //ajout des AC
                foreach ($parcours as $parcour) {
                    $parcour = trim($parcour);
                    if (array_key_exists($parcour, $tabParcours)) {
                        $resSae = new ApcRessourceParcours($res, $tabParcours[$parcour]);
                        $this->entityManager->persist($resSae);
                    }
                }
            }

            $this->entityManager->persist($res);
            $tabRessources[$res->getCodeMatiere()] = $res;
            $ligne++;
        }
        $this->entityManager->flush();

        //Ajout des SAE
        $ligne = 2;
        $sheet = $excel->getSheet(1);//SAE
        while (null !== $sheet->getCellByColumnAndRow(1, $ligne)->getValue()) {
            echo $ligne;
            $sae = new ApcSae();
            $sae->setSemestre($tSemestres[trim($sheet->getCellByColumnAndRow(1, $ligne)->getValue())]);
            $sae->setCodeMatiere(trim($sheet->getCellByColumnAndRow(2, $ligne)->getValue()));
            $sae->setLibelle(trim($sheet->getCellByColumnAndRow(3, $ligne)->getValue()));
            $sae->setLibelleCourt(trim($sheet->getCellByColumnAndRow(4, $ligne)->getValue()));
            $sae->setOrdre(trim($sheet->getCellByColumnAndRow(5, $ligne)->getValue()));

            //compétences
            for ($i = 1; $i <= 6; $i++) {
                if ($sheet->getCellByColumnAndRow(5 + $i, $ligne)->getValue() !== null) {
                    //compétence
                    $acSaeComp = new ApcSaeCompetence($sae, $tCompetences['c'.$i]);
                    $this->entityManager->persist($acSaeComp);

                    $acs = explode(';', $sheet->getCellByColumnAndRow(5 + $i, $ligne)->getValue());
                    //ajout des AC
                    foreach ($acs as $codeAc) {
                        $codeAc = trim($codeAc);
                        if (array_key_exists($codeAc, $tAcs)) {
                            $acRes = new ApcSaeApprentissageCritique($sae, $tAcs[$codeAc]);
                            $this->entityManager->persist($acRes);
                        }
                    }
                }
            }

            //parcours
            if ($sheet->getCellByColumnAndRow(13, $ligne)->getValue() !== null) {
                $parcours = explode(';', $sheet->getCellByColumnAndRow(13, $ligne)->getValue());
                //ajout des AC
                foreach ($parcours as $parcour) {
                    $parcour = trim($parcour);
                    if (array_key_exists($parcour, $tabParcours)) {
                        $resSae = new ApcRessourceParcours($res, $tabParcours[$parcour]);
                        $this->entityManager->persist($resSae);
                    }
                }
            }
            $sae->setObjectifs(trim($sheet->getCellByColumnAndRow(14, $ligne)->getValue()));
            $sae->setDescription(trim($sheet->getCellByColumnAndRow(15, $ligne)->getValue()));
            $sae->setHeuresTotales(Convert::convertToFloat($sheet->getCellByColumnAndRow(16,
                $ligne)->getValue()));//a convertir
            $sae->setTpPpn(Convert::convertToFloat($sheet->getCellByColumnAndRow(17,
                $ligne)->getValue()));//a convertir
            $sae->setProjetPpn(Convert::convertToFloat($sheet->getCellByColumnAndRow(18,
                $ligne)->getValue()));//a convertir
            $sae->setExemples(trim($sheet->getCellByColumnAndRow(19, $ligne)->getValue()));

            $this->entityManager->persist($sae);
            $this->entityManager->flush();

            //Ressources (12)
            if ($sheet->getCellByColumnAndRow(12, $ligne)->getValue() !== null) {
                $ressources = explode(';', $sheet->getCellByColumnAndRow(12, $ligne)->getValue());
                //ajout des AC
                foreach ($ressources as $ressource) {
                    $ressource = trim($ressource);
                    if (array_key_exists($ressource, $tabRessources)) {
                        $sr = $this->entityManager->getRepository(ApcSaeRessource::class)->findOneBy(['ressource' => $tabRessources[$ressource]->getId(), 'sae' => $sae->getId()]);
                        if ($sr === null) {
                            $resSae = new ApcSaeRessource($sae, $tabRessources[$ressource]);
                            $this->entityManager->persist($resSae);
                        }
                    }
                }
            }
            $this->entityManager->flush();
            $ligne++;
        }

    }
}
