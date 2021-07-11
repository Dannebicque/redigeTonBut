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
use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\ApcSaeCompetence;
use App\Entity\ApcSaeRessource;
use App\Entity\ApcSituationProfessionnelle;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Utils\Codification;
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
        dump($tCompetences);
//        die();
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
                for($i = 5; $i<=7; $i++) {
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
            $ligne ++;
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
        $tCompetences = $this->entityManager->getRepository(ApcCompetence::class)->findOneByDepartementArray($this->departement);
        foreach ($xml->semestre as $sem) {
            $semestre = $this->entityManager->getRepository(Semestre::class)->findOneByDepartementEtNumero($this->departement,
                $sem['numero'], $sem['ordreAnnee']);

            if (null !== $semestre) {
                $tRessources = [];
                foreach ($sem->ressources->ressource as $ressource) {
                    $ar = new ApcRessource();
                    $ar->setSemestre($semestre);
                    $ar->setOrdre((int)substr($ressource['code'], 2, 2));
                    $ar->setLibelle($ressource->titre);
                    $ar->setTdPpn($ressource['heuresCMTD']);
                    $ar->setTpPpn($ressource['heuresTP']);
                    $ar->setDescription((string)$ressource->description);
                    $ar->setMotsCles((string)$ressource->motsCles);
                    $ar->setCodeMatiere(Codification::codeRessource($ar));
                    $this->entityManager->persist($ar);
                    $tRessources[$ar->getCodeMatiere()] = $ar;

                    //acs
                    foreach ($ressource->acs->ac as $ac) {
                        $rac = new ApcRessourceApprentissageCritique($ar, $tAcs[trim((string)$ac)]);
                        $this->entityManager->persist($rac);
                    }
                    //competences
                    foreach ($ressource->competences->competence as $comp) {
                        $rac = new ApcRessourceCompetence($ar, $tCompetences[trim((string)$comp['nom'])]);
                        $rac->setCoefficient((float)$comp['coefficient']);
                        $this->entityManager->persist($rac);
                    }
                    //les saes seront ajoutée par les SAE
                }

                foreach ($sem->saes->sae as $sae) {
                    $ar = new ApcSae();
                    $ar->setSemestre($semestre);
                    $ar->setLibelle($sae->titre);
                    $ar->setOrdre((int)substr($sae['code'], 4, 2));
                    $ar->setTdPpn((float)$sae['heuresCMTD']);
                    $ar->setTpPpn((float)$sae['heuresTP']);
                    $ar->setProjetPpn((float)$sae['heuresProjet']);
                    $ar->setDescription((string)$sae->description);
                    $ar->setExemples((string)$sae->exemples);
                    $ar->setLivrables((string)$sae->livrables);
                    $ar->setCodeMatiere(Codification::codeSae($ar));

                    $this->entityManager->persist($ar);

                    //acs
                    foreach ($sae->acs->ac as $ac) {
                        $rac = new ApcSaeApprentissageCritique($ar, $tAcs[trim((string)$ac)]);
                        $this->entityManager->persist($rac);
                    }

                    //competences
                    foreach ($sae->competences->competence as $comp) {
                        $rac = new ApcSaeCompetence($ar, $tCompetences[trim((string)$comp['nom'])]);
                        $rac->setCoefficient((float)$comp['coefficient']);
                        $this->entityManager->persist($rac);
                    }
                    //Ressources
                    foreach ($sae->ressources->ressource as $comp) {
                        $rac = new ApcSaeRessource($ar, $tRessources[trim((string)$comp)]);
                        $this->entityManager->persist($rac);
                    }
                }
            }
        }
        $this->entityManager->flush();
    }

    private function openExcelFile()
    {
        $reader = new Xlsx();

        return $reader->load($this->fichier);
    }
}
