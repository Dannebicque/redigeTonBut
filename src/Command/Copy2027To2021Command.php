<?php

namespace App\Command;

use App\Entity\Annee;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcCompetenceSemestre;
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
use App\Entity\Version;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:copy-2027-to-2021',
    description: 'Copies data from the 2027 database to the 2021 database',
)]
class Copy2027To2021Command extends Command
{
    private ObjectManager $emCible;
    private ObjectManager $em2027;
    private array $mapping = [];
    private Connection $conn2027;

    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct();
        // Le manager qui écrit dans la base de destination
        $this->emCible = $doctrine->getManager('default');

        // Le manager qui lit dans la base 2027
        $this->em2027 = $doctrine->getManager('import');

        /** @var Connection $connImport */
        $this->conn2027 = $this->em2027->getConnection();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. Création des Versions pour chaque département
        //comme les deux BDD on les mêmes départements sur la même id, je peux partir de 2027 pour construire les versions et reprendre les données 2027 pour cette version
        $departements = $this->em2027->getRepository(Departement::class)->findAll();
        $versions2027 = [];

        $output->writeln("Création des Versions 2027...");
        foreach ($departements as $dept) {
            // On vérifie si la version existe déjà pour éviter les doublons
            $version = $this->emCible->getRepository(Version::class)->findOneBy([
                'departement' => $dept,
                'libelle' => 'Programme 2027'
            ]);

            if (!$version) {
                $version = new Version($dept);
                $version->setLibelle('Programme 2027');
                $version->setEtatPublication('Brouillon');
                $version->setAnnee(2027);
                $version->setActif(false); // On la laisse inactive le temps de l'import
                $version->setTextePresentation($dept->getTextePresentation() ?? '');
                $version->setAltBut1($dept->getAltBut1());
                $version->setAltBut2($dept->getAltBut2());
                $version->setAltBut3($dept->getAltBut3());
                $this->emCible->persist($version);
            }
            $versions2027[$dept->getId()] = $version;
        }
        $this->emCible->flush();
        $output->writeln("✅ Versions 2027 créées.");

        // 2. Importation des Parcours
        $this->importParcours($output, $versions2027);
//
//        // 3. Importation des Compétences
        $this->importCompetences($output, $versions2027);
        $this->importComposantesEssentielles($output);
        $this->importSituationsProfessionnelles($output);
//
//        // 4. Importation des Années
        $this->importAnnees($output, $versions2027);
        $this->importSemestres($output);
        $this->importNiveaux($output);
        $this->importParcoursNiveaux($output);
        $this->importApprentissagesCritiques($output);
        $this->importCompetencesSemestres($output);

        // traitement ressource et SAE
        $this->importRessources($output);
        $this->importSaes($output);

        $output->writeln("<info>Migration terminée avec succès !</info>");
        return Command::SUCCESS;
    }

    private function importParcours($output, $versions2027): void
    {
        $output->writeln("Création des Parcours 2027...");

        // $oldNiveaux = $this->em2027->getRepository(ApcParcours::class)->findAll();
        $oldNiveaux = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_parcours p');
        foreach ($oldNiveaux as $old) {
            $deptId = isset($old['departement_id']) ? (int)$old['departement_id'] : null;
            if ($deptId !== null && isset($versions2027[$deptId])) {
                $new = new ApcParcours();
                $new->setLibelle($old['libelle']);
                $new->setCode($old['code']);
                $new->setOrdre($old['ordre']);
                $new->setCouleur($old['couleur']);
                $new->setDispense($old['dispense']);
                $new->setModalitesParticulieres($old['modalites_particulieres']);
                $new->setNumeroIdentifiant($old['numero_identifiant']);
                $new->setTextePresentation($old['texte_presentation'] ?? '');

                // On raccroche à la nouvelle version via le département
                $new->setVersion($versions2027[$deptId]);
                $oldId = (int)$old['id'];
                $this->emCible->persist($new);
                $this->mapping[ApcParcours::class][$oldId] = $new;
            }
        }
        $this->emCible->flush();
        $output->writeln("✅ Parcours importés.");
    }

    private function importCompetences($output, $versions2027): void
    {
        $output->writeln("Création des compétences 2027...");
        $oldComps = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_competence p');

        foreach ($oldComps as $old) {
            $deptId = isset($old['departement_id']) ? (int)$old['departement_id'] : null;
            if ($deptId !== null && isset($versions2027[$deptId])) {
                $new = new ApcCompetence($versions2027[$deptId]);
                $new->setLibelle($old['libelle']);
                $new->setNumeroIdentifiant($old['numero_identifiant']);
                $new->setNumero($old['numero']);
                $new->setCouleur($old['couleur']);
                $new->setNomCourt($old['nom_court']);

                $this->emCible->persist($new);
                $this->mapping[ApcCompetence::class][(int)$old['id']] = $new;
            }
        }
        $this->emCible->flush();
        $output->writeln("✅ Compétences importées.");
    }

    private function importAnnees($output, $versions2027): void
    {
        $output->writeln("Création des années 2027...");

        $oldAnnees = $this->conn2027->fetchAllAssociative('SELECT p.* FROM annee p');

        foreach ($oldAnnees as $old) {
            $deptId = isset($old['departement_id']) ? (int)$old['departement_id'] : null;
            if ($deptId !== null && isset($versions2027[$deptId])) {
                $new = new Annee();
                $new->setVersion($versions2027[$deptId]);
                $new->setDepartement($versions2027[$deptId]->getDepartement());
                $new->setLibelle($old['libelle']);
                $new->setOrdre($old['ordre']);
                $new->setCodeEtape($old['code_etape']);
                $new->setLibelleLong($old['libelle_long']);

                $this->emCible->persist($new);
                $this->mapping[Annee::class][(int)$old['id']] = $new;
            }
        }
        $this->emCible->flush();
        $output->writeln("✅ Années importées.");
    }

    private function importSemestres(OutputInterface $output): void
    {
        $output->writeln("Création des semestres 2027 ...");
        // On récupère les semestres de la base 2027
        $oldSemestres = $this->conn2027->fetchAllAssociative('SELECT p.* FROM semestre p');
        $pendingLinks = [];

        foreach ($oldSemestres as $oldSemestre) {
            $oldAnnee = isset($oldSemestre['annee_id']) ? (int)$oldSemestre['annee_id'] : null;
            $oldParcours = isset($oldSemestre['apc_parcours_id']) ? (int)$oldSemestre['apc_parcours_id'] : null;

            $newSemestre = new Semestre();
            $newSemestre->setLibelle($oldSemestre['libelle']);
            if ($oldAnnee && isset($this->mapping[Annee::class][$oldAnnee])) {
                // On raccordement au nouvel objet Annee créé à l'étape précédente
                $newSemestre->setAnnee($this->mapping[Annee::class][$oldAnnee]);
            }

            if ($oldParcours && isset($this->mapping[Annee::class][$oldParcours])) {
                // On raccordement au nouvel objet Annee créé à l'étape précédente
                $newSemestre->setApcParcours($this->mapping[ApcParcours::class][$oldParcours]);
            }
            $newSemestre->setNbDemiJournees($oldSemestre['nb_demi_journees']);
            $newSemestre->setNbSemaines($oldSemestre['nb_semaines']);
            $newSemestre->setNbHeuresEnseignementLocale($oldSemestre['nb_heures_enseignement_locale']);
            $newSemestre->setNbHeuresEnseignementRessourceLocale($oldSemestre['nb_heures_enseignement_ressource_locale']);
            $newSemestre->setNbHeuresEnseignementRessourceNational($oldSemestre['nb_heures_enseignement_ressource_national']);
            $newSemestre->setNbHeuresEnseignementSaeLocale($oldSemestre['nb_heures_enseignement_sae_locale']);
            $newSemestre->setNbHeuresProjet($oldSemestre['nb_heures_projet']);
            $newSemestre->setNbHeuresRessourceSae($oldSemestre['nb_heures_ressource_sae']);
            $newSemestre->setNbHeuresTpLocale($oldSemestre['nb_heures_tp_locale']);
            $newSemestre->setNbHeuresTpNational($oldSemestre['nb_heures_tp_national']);
            $newSemestre->setNbSemainesConges($oldSemestre['nb_semaines_conges']);
            $newSemestre->setNbSemainesStageMax($oldSemestre['nb_semaines_stage_max']);
            $newSemestre->setNbSemaineStageMin($oldSemestre['nb_semaine_stage_min']);
            $newSemestre->setOrdreAnnee($oldSemestre['ordre_annee']);
            $newSemestre->setOrdreLmd($oldSemestre['ordre_lmd']);
            $newSemestre->setPourcentageAdaptationLocale($oldSemestre['pourcentage_adaptation_locale']);
            $newSemestre->setVhNbHeuresDontTpSaeRessource($oldSemestre['vh_nb_heures_dont_tp_sae_ressource']);
            $newSemestre->setVhNbHeuresEnseignementSae($oldSemestre['vh_nb_heures_enseignement_sae']);
            $newSemestre->setVhNbHeuresEnseignementSaeRessource($oldSemestre['vh_nb_heures_enseignement_sae_ressource']);
            $newSemestre->setVhNbHeuresProjetTutore($oldSemestre['vh_nb_heures_projet_tutore']);
            // Mémoriser les ids de précédent/suivant pour une résolution ultérieure
            $oldId = (int)$oldSemestre['id'];
            $pendingLinks[$oldId] = [
                'precedent' => isset($oldSemestre['precedent_id']) ? (int)$oldSemestre['precedent_id'] : null,
                'suivant' => isset($oldSemestre['suivant_id']) ? (int)$oldSemestre['suivant_id'] : null,
            ];

            $this->emCible->persist($newSemestre);
            $this->mapping[Semestre::class][(int)$oldSemestre['id']] = $newSemestre;
        }

        foreach ($pendingLinks as $oldId => $links) {
            $newSem = $this->mapping[Semestre::class][$oldId] ?? null;
            if (!$newSem) {
                continue;
            }

            if ($links['precedent'] !== null && isset($this->mapping[Semestre::class][$links['precedent']])) {
                $newSem->setPrecedent($this->mapping[Semestre::class][$links['precedent']]);
            } else {
                $newSem->setPrecedent(null);
            }

            if ($links['suivant'] !== null && isset($this->mapping[Semestre::class][$links['suivant']])) {
                $newSem->setSuivant($this->mapping[Semestre::class][$links['suivant']]);
            } else {
                $newSem->setSuivant(null);
            }

            $this->emCible->persist($newSem);
        }

        $this->emCible->flush();
        $output->writeln("✅ Semestres migrés et rattachés aux nouvelles années.");
    }

    private function importNiveaux(OutputInterface $output): void
    {
        $output->writeln("Création des niveaux 2027 ...");
        $oldNiveaux = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_niveau p');

        foreach ($oldNiveaux as $oldNiveau) {
            $oldAnnee = isset($oldNiveau['annee_id']) ? (int)$oldNiveau['annee_id'] : null;
            $oldCompentence = isset($oldNiveau['competence_id']) ? (int)$oldNiveau['competence_id'] : null;

            $newNiveau = new ApcNiveau();

            if ($oldAnnee && isset($this->mapping[Annee::class][$oldAnnee])) {
                $newNiveau->setAnnee($this->mapping[Annee::class][$oldAnnee]);
            }

            if ($oldCompentence && isset($this->mapping[ApcCompetence::class][$oldCompentence])) {
                $newNiveau->setCompetence($this->mapping[ApcCompetence::class][$oldCompentence]);
            }
            $newNiveau->setLibelle($oldNiveau['libelle']);
            $newNiveau->setOrdre($oldNiveau['ordre']);

            $this->emCible->persist($newNiveau);

            // On mémorise le semestre pour les Ressources et les SAE qui viendront après
            $this->mapping[ApcNiveau::class][(int)$oldNiveau['id']] = $newNiveau;
        }


        $this->emCible->flush();
        $output->writeln("✅ Niveaux migrés et rattachés aux nouvelles années.");
    }

    private function importParcoursNiveaux(OutputInterface $output): void
    {
        $output->writeln("Création liaison Parcours/Niveaux 2027 ...");
        $oldNiveaux = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_parcours_niveau p');

        foreach ($oldNiveaux as $old) {
            $oldNiveau = isset($old['niveau_id']) ? (int)$old['niveau_id'] : null;
            $oldParcours = isset($old['parcours_id']) ? (int)$old['parcours_id'] : null;
            // On récupère les semestres de la base 2027
            $newNiveau = new ApcParcoursNiveau();
            if ($oldParcours && isset($this->mapping[ApcParcours::class][$oldParcours])) {
                $newNiveau->setParcours($this->mapping[ApcParcours::class][$oldParcours]);
            }

            if ($oldNiveau && isset($this->mapping[ApcNiveau::class][$oldNiveau])) {
                $newNiveau->setNiveau($this->mapping[ApcNiveau::class][$oldNiveau]);
            }

            $this->emCible->persist($newNiveau);

            // On mémorise le semestre pour les Ressources et les SAE qui viendront après
            $this->mapping[ApcParcoursNiveau::class][(int)$old['id']] = $newNiveau;
        }


        $this->emCible->flush();
        $output->writeln("✅ Liaisons Parcours/Niveaux migrés et rattachés aux nouvelles années.");
    }

    private function importCompetencesSemestres(OutputInterface $output): void
    {
        $output->writeln("Création liaison Competences/Semestres 2027 ...");
        $oldNiveaux = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_competence_semestre p');

        foreach ($oldNiveaux as $oldNiveau) {
            $newCompSemestre = new ApcCompetenceSemestre();

            $oldCompetence = isset($oldNiveau['competence_id']) ? (int)$oldNiveau['competence_id'] : null;
            $oldSemestre = isset($oldNiveau['semestre_id']) ? (int)$oldNiveau['semestre_id'] : null;
            // On récupère les semestres de la base 202

            if ($oldCompetence && isset($this->mapping[ApcCompetence::class][$oldCompetence])) {
                $newCompSemestre->setCompetence($this->mapping[ApcCompetence::class][$oldCompetence]);
            }

            if ($oldSemestre && isset($this->mapping[Semestre::class][$oldSemestre])) {
                $newCompSemestre->setSemestre($this->mapping[Semestre::class][$oldSemestre]);
            }
            $newCompSemestre->setECTS($oldNiveau['ects']);
            //todo: a traiter car id de parcours en dur dans le tableau
            $newCompSemestre->setEctsParcours($oldNiveau['ects_parcours'] !== "" ? json_decode($oldNiveau['ects_parcours'], true) : []);

            $this->emCible->persist($newCompSemestre);

            // On mémorise le semestre pour les Ressources et les SAE qui viendront après
            $this->mapping[ApcCompetenceSemestre::class][(int)$oldNiveau['id']] = $newCompSemestre;
        }


        $this->emCible->flush();
        $output->writeln("✅ Liaisons Competences/Semestres migrés et rattachés aux nouvelles années.");
    }

    private function importApprentissagesCritiques(OutputInterface $output): void
    {
        $output->writeln("Création Apprentissages critiques 2027 ...");
        $oldAcs = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_apprentissage_critique p');

        foreach ($oldAcs as $oldAc) {

            $oldNiveau = isset($oldAc['niveau_id']) ? (int)$oldAc['niveau_id'] : null;

            $newAc = new ApcApprentissageCritique();
            $newAc->setLibelle($oldAc['libelle']);
            $newAc->setOrdre($oldAc['ordre']);
            $newAc->setCode($oldAc['code']);

            if ($oldNiveau && isset($this->mapping[ApcNiveau::class][$oldNiveau])) {
                $newAc->setNiveau($this->mapping[ApcNiveau::class][$oldNiveau]);
            }

            $this->emCible->persist($newAc);

            // On mémorise le semestre pour les Ressources et les SAE qui viendront après
            $this->mapping[ApcApprentissageCritique::class][(int)$oldAc['id']] = $newAc;
        }

        $this->emCible->flush();
        $output->writeln("✅ Apprentissages critiques migrés et rattachés aux nouvelles années.");
    }

    private function importComposantesEssentielles(OutputInterface $output): void
    {
        $output->writeln("Création des composantes essentielles 2027 ...");
        $oldCompos = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_composante_essentielle p');

        foreach ($oldCompos as $oldCompo) {
            $oldCompetence = isset($oldCompo['competence_id']) ? (int)$oldCompo['competence_id'] : null;
            $newCompo = new ApcComposanteEssentielle();
            $newCompo->setLibelle($oldCompo['libelle']);
            $newCompo->setCode($oldCompo['code']);
            $newCompo->setOrdre($oldCompo['ordre']);

            if ($oldCompetence && isset($this->mapping[ApcCompetence::class][$oldCompetence])) {
                $newCompo->setCompetence($this->mapping[ApcCompetence::class][$oldCompetence]);
            }

            $this->emCible->persist($newCompo);

            // On mémorise le semestre pour les Ressources et les SAE qui viendront après
            $this->mapping[ApcComposanteEssentielle::class][(int)$oldCompo['id']] = $newCompo;
        }

        $this->emCible->flush();
        $output->writeln("✅ Composantes essentielles migrées et rattachées aux nouvelles années.");
    }

    private function importSituationsProfessionnelles(OutputInterface $output): void
    {
        $output->writeln("Création des situations professionnelles 2027 ...");
        $oldCompos = $this->conn2027->fetchAllAssociative('SELECT p.* FROM apc_situation_professionnelle p');

        foreach ($oldCompos as $oldCompo) {
            $oldCompetence = isset($oldCompo['competence_id']) ? (int)$oldCompo['competence_id'] : null;
            $newCompo = new ApcSituationProfessionnelle();
            $newCompo->setLibelle($oldCompo['libelle']);
            if ($oldCompetence && isset($this->mapping[ApcCompetence::class][$oldCompetence])) {
                // On raccordement au nouvel objet Annee créé à l'étape précédente
                $newCompo->setCompetence($this->mapping[ApcCompetence::class][$oldCompetence]);
            }

            $this->emCible->persist($newCompo);

            // On mémorise le semestre pour les Ressources et les SAE qui viendront après
            $this->mapping[ApcSituationProfessionnelle::class][(int)$oldCompo['id']] = $newCompo;
        }

        $this->emCible->flush();
        $output->writeln("✅ Situations professionnelles migrées et rattachées aux nouvelles années.");
    }


// Dans `src/Command/Copy2027To2021Command.php`

    private function importRessources(OutputInterface $output): void
    {
        $output->writeln("Création des ressources 2027 ... ");

        // 1) Ressources (table principale)
        $rows = $this->conn2027->fetchAllAssociative('SELECT r.* FROM apc_ressource r');
        foreach ($rows as $row) {
            $new = new ApcRessource();
            $new->setLibelle($row['libelle']);
            $new->setOrdre($row['ordre']);

            $new->setficheAdaptationLocale(isset($row['fiche_adaptation_locale']) ? (bool)$row['fiche_adaptation_locale'] : null);
            $new->setMotsCles($row['mots_cles'] ?? null);

            $new->setCmPreco(isset($row['cm_preco']) ? (float)$row['cm_preco'] : null);
            $new->setTdPreco(isset($row['td_preco']) ? (float)$row['td_preco'] : null);
            $new->setTpPreco(isset($row['tp_preco']) ? (float)$row['tp_preco'] : null);

            $new->setCmPpn(isset($row['cm_ppn']) ? (float)$row['cm_ppn'] : null);
            $new->setTdPpn(isset($row['td_ppn']) ? (float)$row['td_ppn'] : null);
            $new->setTpPpn(isset($row['tp_ppn']) ? (float)$row['tp_ppn'] : null);

            $new->setCodeMatiere($row['code_matiere'] ?? null);
            $new->setCommentaire($row['commentaire'] ?? null);
            $new->setDescription($row['description'] ?? null);
            $new->setHeuresTotales(isset($row['heures_totales']) ? (int)$row['heures_totales'] : null);
            $new->setLibelleCourt($row['libelle_court'] ?? null);

            // FK semestre
            $oldSemestreId = isset($row['semestre_id']) ? (int)$row['semestre_id'] : null;
            if ($oldSemestreId !== null && isset($this->mapping[Semestre::class][$oldSemestreId])) {
                $new->setSemestre($this->mapping[Semestre::class][$oldSemestreId]);
            }

            $this->emCible->persist($new);
            $this->mapping[ApcRessource::class][(int)$row['id']] = $new;
        }
        $this->emCible->flush();

        // 2) Liaison ressources/competences (table pivot)
        $output->writeln("... Recopie des liens ressources/competences ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT rc.* FROM apc_ressource_competence rc');
        foreach ($rows as $row) {
            $oldRessourceId = isset($row['ressource_id']) ? (int)$row['ressource_id'] : null;
            $oldCompetenceId = isset($row['competence_id']) ? (int)$row['competence_id'] : null;

            if (
                !isset($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcCompetence::class][$oldCompetenceId]) || $oldRessourceId === null || $oldCompetenceId === null
            ) {
                continue;
            }

            $new = new ApcRessourceCompetence($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcCompetence::class][$oldCompetenceId]);
            $new->setCoefficient($row['coefficient'] ?? null);

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens ressources/competences ... ");

        // 3) Liaison ressources/apprentissages critiques (table pivot)
        $output->writeln("... Recopie des liens ressources/AC ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT ra.* FROM apc_ressource_apprentissage_critique ra');
        foreach ($rows as $row) {
            $oldRessourceId = isset($row['ressource_id']) ? (int)$row['ressource_id'] : null;
            $oldAcId = isset($row['apprentissage_critique_id']) ? (int)$row['apprentissage_critique_id'] : null;

            if (
                !isset($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcApprentissageCritique::class][$oldAcId]) || $oldRessourceId === null || $oldAcId === null
            ) {
                continue;
            }

            $new = new ApcRessourceApprentissageCritique($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcApprentissageCritique::class][$oldAcId]);

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens ressources/AC ... ");

        // 4) Liaison ressources/parcours (table pivot)
        $output->writeln("... Recopie des liens ressources/Parcours ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT rp.* FROM apc_ressource_parcours rp');
        foreach ($rows as $row) {
            $oldRessourceId = isset($row['ressource_id']) ? (int)$row['ressource_id'] : null;
            $oldParcoursId = isset($row['parcours_id']) ? (int)$row['parcours_id'] : null;

            if (
                !isset($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcParcours::class][$oldParcoursId]) || $oldRessourceId === null || $oldParcoursId === null
            ) {
                continue;
            }

            $new = new ApcRessourceParcours($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcParcours::class][$oldParcoursId]);

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens ressources/Parcours ... ");

        // 5) Auto\-référence ressources prérequisites (many\-to\-many)
        // Table pivot typique: apc_ressource_prerequise(ressource_id, prerequise_id)
        $output->writeln("... Recopie des liens ressources/ressources (pré\-requis) ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT rr.* FROM apc_ressource_apc_ressource rr');

        foreach ($rows as $row) {
            $oldRessourceId = isset($row['apc_ressource_source ']) ? (int)$row['apc_ressource_source '] : null;
            $oldPrereqId = isset($row['apc_ressource_target ']) ? (int)$row['apc_ressource_target '] : null;

            if (
                !isset($this->mapping[ApcRessource::class][$oldRessourceId], $this->mapping[ApcRessource::class][$oldPrereqId]) || $oldRessourceId === null || $oldPrereqId === null
            ) {
                continue;
            }

            $newRessource = $this->mapping[ApcRessource::class][$oldRessourceId];
            $newPrereq = $this->mapping[ApcRessource::class][$oldPrereqId];

            $newRessource->addRessourcesPreRequise($newPrereq);
        }

        $this->emCible->flush();
        $output->writeln("✅ Ressources migrées et relations recopiées.");
    }

    private function importSaes(OutputInterface $output): void
    {
        $output->writeln("Création des SAES 2027 ... ");

        // 1) SAEs (table principale)
        $rows = $this->conn2027->fetchAllAssociative('SELECT s.* FROM apc_sae s');
        foreach ($rows as $row) {
            $new = new ApcSae();
            $new->setLibelle($row['libelle']);
            $new->setOrdre($row['ordre']);
            $new->setLibelleCourt($row['libelle_court'] ?? null);
            $new->setCodeMatiere($row['code_matiere'] ?? null);
            $new->setCommentaire($row['commentaire'] ?? null);
            $new->setDescription($row['description'] ?? null);
            $new->setHeuresTotales(isset($row['heures_totales']) ? (int)$row['heures_totales'] : null);
            $new->setCmPpn(isset($row['cm_ppn']) ? (float)$row['cm_ppn'] : null);
            $new->setTdPpn(isset($row['td_ppn']) ? (float)$row['td_ppn'] : null);
            $new->setTpPpn(isset($row['tp_ppn']) ? (float)$row['tp_ppn'] : null);

            $new->setExemples(isset($row['exemples']) ? (int)$row['exemples'] : null);
            $new->setFicheAdaptationLocale(isset($row['fiche_adaptation_locale']) ? (bool)$row['fiche_adaptation_locale'] : null);
            $new->setObjectifs(isset($row['objectifs']) ? (bool)$row['objectifs'] : '');
            $new->setPortfolio(isset($row['portfolio']) && (bool)$row['portfolio']);
            $new->setStage(isset($row['stage']) && (bool)$row['stage']);
            $new->setProjetPpn(isset($row['projet_ppn']) && (bool)$row['projet_ppn']);

            // FK semestre
            $oldSemestreId = isset($row['semestre_id']) ? (int)$row['semestre_id'] : null;
            if ($oldSemestreId !== null && isset($this->mapping[Semestre::class][$oldSemestreId])) {
                $new->setSemestre($this->mapping[Semestre::class][$oldSemestreId]);
            }

            $this->emCible->persist($new);
            $this->mapping[ApcSae::class][(int)$row['id']] = $new;
        }
        $this->emCible->flush();

        // 2) Liaison SAEs/competences (table pivot)
        $output->writeln("... Recopie des liens SAEs/competences ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT sc.* FROM apc_sae_competence sc');
        foreach ($rows as $row) {
            $oldSaeId = isset($row['sae_id']) ? (int)$row['sae_id'] : null;
            $oldCompetenceId = isset($row['competence_id']) ? (int)$row['competence_id'] : null;

            if (
                !isset($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcCompetence::class][$oldCompetenceId]) || $oldSaeId === null || $oldCompetenceId === null
            ) {
                continue;
            }

            $new = new ApcSaeCompetence($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcCompetence::class][$oldCompetenceId]);
            $new->setCoefficient($row['coefficient'] ?? null);

            // Champs additionnels éventuels
            if (array_key_exists('coefficient', $row) && method_exists($new, 'setCoefficient')) {
                $new->setCoefficient($row['coefficient']);
            }

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens SAEs/competences ... ");

        // 3) Liaison SAEs/AC (table pivot)
        $output->writeln("... Recopie des liens SAEs/AC ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT sa.* FROM apc_sae_apprentissage_critique sa');
        foreach ($rows as $row) {
            $oldSaeId = isset($row['sae_id']) ? (int)$row['sae_id'] : null;
            $oldAcId = isset($row['apprentissage_critique_id']) ? (int)$row['apprentissage_critique_id'] : null;

            if (
                !isset($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcApprentissageCritique::class][$oldAcId]) || $oldSaeId === null || $oldAcId === null
            ) {
                continue;
            }

            $new = new ApcSaeApprentissageCritique($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcApprentissageCritique::class][$oldAcId]);

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens SAEs/AC ... ");

        // 4) Liaison SAEs/Parcours (table pivot)
        $output->writeln("... Recopie des liens SAEs/Parcours ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT sp.* FROM apc_sae_parcours sp');
        foreach ($rows as $row) {
            $oldSaeId = isset($row['sae_id']) ? (int)$row['sae_id'] : null;
            $oldParcoursId = isset($row['parcours_id']) ? (int)$row['parcours_id'] : null;

            if (
                !isset($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcParcours::class][$oldParcoursId]) || $oldSaeId === null || $oldParcoursId === null
            ) {
                continue;
            }

            $new = new ApcSaeParcours($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcParcours::class][$oldParcoursId]);

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens SAEs/Parcours ... ");

        // 5) Liaison SAEs/Ressources (table pivot)
        $output->writeln("... Recopie des liens SAEs/Ressources ... ");
        $rows = $this->conn2027->fetchAllAssociative('SELECT sr.* FROM apc_sae_ressource sr');
        foreach ($rows as $row) {
            $oldSaeId = isset($row['sae_id']) ? (int)$row['sae_id'] : null;
            $oldRessourceId = isset($row['ressource_id']) ? (int)$row['ressource_id'] : null;

            if (
                !isset($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcRessource::class][$oldRessourceId]) || $oldSaeId === null || $oldRessourceId === null
            ) {
                continue;
            }

            $new = new ApcSaeRessource($this->mapping[ApcSae::class][$oldSaeId], $this->mapping[ApcRessource::class][$oldRessourceId]);

            // Champs additionnels éventuels
            if (array_key_exists('coefficient', $row) && method_exists($new, 'setCoefficient')) {
                $new->setCoefficient($row['coefficient']);
            }

            $this->emCible->persist($new);
        }
        $this->emCible->flush();
        $output->writeln("... Fin Recopie des liens SAEs/Ressources ... ");

        $output->writeln("✅ Saes migrées et relations recopiées.");
    }
}
