<?php

namespace App\Command;

use App\Entity\ApcCompetence;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\ApcSaeCompetence;
use App\Entity\ApcSaeParcours;
use App\Entity\Semestre;
use App\Entity\Version;
use App\Repository\ApcNiveauRepository;
use App\Repository\VersionRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:sae:create-pae',
    description: 'Crée une SAÉ PAE par compétence pour tous les semestres d\'une version donnée (2027 par défaut).',
)]
class CreatePaeSaeCommand extends Command
{
    private const int DEFAULT_ANNEE_VERSION = 2027;
    private const string PAE_LIBELLE = 'Portfolio d’Apprentissage et d’Évaluation (PAÉ) : Démarche PAÉ';
    private const string PAE_MARKER_PREFIX = '__AUTO_PAE_';

    // Remplacer librement ces textes par le contenu métier attendu (par semestre).
    private function getPaeObjectifsTemplate(int $ordreLmd): string
    {
        return match ($ordreLmd) {
            1 => <<<'TEXT'
Au semestre 1, l’objectif premier est l’acquisition de la démarche PAÉ. Cette démarche conduit à une première production concernant a minima une compétence. Cette production permet à l’étudiant de se positionner sans évaluation sommative sur le niveau de compétence acquis. L’enjeu est de permettre à l’étudiant d’engager une démarche d’auto-positionnement et d’auto-évaluation. Le PAÉ produit permet à l’équipe pédagogique d’évaluer de manière sommative l’appropriation de la démarche PAÉ. 
TEXT,
            2, 4, 6 => <<<'TEXT'
Aux semestres pairs, la démarche PAÉ permet d’évaluer l’atteinte par l’étudiant du niveau attendu dans le référentiel de compétences. L’évaluation repose sur sa capacité à en faire la démonstration en s’appuyant sur des éléments de preuve argumentés et sélectionnés. L’étudiant adopte une posture réflexive et critique sur le travail mené en SAÉ.

L’évaluation repose sur les 5 critères suivants :
 * Qualité des actions, de la démarche ;
 * Qualité des résultats (recevabilité par les professionnels du domaine) ;
 * Qualité des justifications sur la base des ressources mobilisées ; 
 * Recul critique ;
 * Capacité à s’adapter à la situation / à réguler / à transposer à d’autres situations.
 
Ces deux derniers critères doivent être appréciés en leur accordant une importance croissante au fur et à mesure de la formation.
TEXT,
            3, 5 => <<<'TEXT'
Aux semestres impairs, la démarche conduit à la production d’un PAÉ intermédiaire. Il permet à l’étudiant de se positionner, grâce à une évaluation formative, au regard du niveau de compétences attendu de son année du B.U.T. et relativement au parcours suivi.

L’acquisition de cette démarche PAÉ pour les étudiants en passerelle entrante est indispensable. Elle prend appui sur la fiche ressource du semestre 1.
TEXT,
            default => <<<'TEXT'
Texte imposé des objectifs de la SAÉ PAE à personnaliser dans la commande.

Cette SAÉ est générée automatiquement pour la compétence : %s.
TEXT,
        };
    }

    private function getPaeDescriptionTemplate(int $ordreLmd): string
    {
        return match ($ordreLmd) {
            1 => <<<'TEXT'
La démarche PAÉ nécessite que l’étudiant documente et argumente sa trajectoire de développement.

L’équipe pédagogique accompagne l’étudiant dans la compréhension et l’appropriation effectives de l’ensemble du référentiel de compétences et de ses éléments constitutifs : composantes essentielles, familles de situations, niveaux, apprentissages critiques.

Un focus est effectué sur les composantes essentielles en tant que critères permettant de juger de la qualité du travail effectué, quel que soit le niveau de compétence considéré.

L’équipe pédagogique présente les différentes possibilités de démonstration du développement de la compétence, s’appuyant sur des éléments de preuve issus des SAÉ.

La constitution de preuves s’effectue en collectant, sélectionnant, mobilisant et analysant des traces d’activités pertinentes et significatives. L’analyse de ces traces repose sur les réponses aux questions ‘qui, quand, quoi’ et ‘où’ qui permettent de les contextualiser. Les réponses personnelles aux questions ‘pourquoi’ et ‘comment’ permettent de faire la preuve de la montée en compétences de chaque étudiant.
TEXT,
            2, 4, 6 => <<<'TEXT'
Prenant n’importe quelle forme, littérale, analogique ou numérique, la démarche PAÉ peut être menée dans le cadre d’ateliers. L’étudiant retrace sa trajectoire durant son année du B.U.T. au regard du niveau attendu du référentiel de compétences. Pour cela, il adopte une posture propice à une analyse distanciée et intégrative de l’ensemble des SAÉ
TEXT,
            3, 5 => <<<'TEXT'
Chaque début d’année, un focus est réalisé sur la description du niveau de compétences visé pour l’année. 

À partir du semestre 3, le processus mis en œuvre en première année de B.U.T. est reproduit à l’identique chaque semestre. Si de nouvelles compétences apparaissent, il convient de revenir sur le référentiel de compétences.
TEXT,
            default => <<<'TEXT'
Texte descriptif imposé de la SAÉ PAE à personnaliser dans la commande.

Compétence associée : %s.
TEXT,
        };
    }

    public function __construct(
        private readonly VersionRepository $versionRepository,
        private readonly ApcNiveauRepository $apcNiveauRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'annee',
                null,
                InputOption::VALUE_REQUIRED,
                'Année de version à traiter.',
                self::DEFAULT_ANNEE_VERSION,
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Prévisualise les créations sans écrire en base.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $anneeVersion = (int) $input->getOption('annee');
        $dryRun = (bool) $input->getOption('dry-run');

        $versions = $this->versionRepository->findByVersion($anneeVersion);
        if ($versions === []) {
            $io->warning(sprintf('Aucune version %d trouvée.', $anneeVersion));

            return Command::SUCCESS;
        }

        $stats = [
            'versions' => 0,
            'semestres' => 0,
            'competences' => 0,
            'created' => 0,
            'skipped' => 0,
        ];

        $connection = $this->entityManager->getConnection();

        try {
            if ($dryRun === false) {
                $connection->beginTransaction();
            }

            /** @var Version $version */
            foreach ($versions as $version) {
                ++$stats['versions'];
                $io->section(sprintf(
                    '%s - %s (%d)',
                    $version->getDepartement()?->getSigle(),
                    $version->getLibelle(),
                    $version->getAnnee(),
                ));

                $semestres = $version->getSemestres();
                usort(
                    $semestres,
                    static function (Semestre $left, Semestre $right): int {
                        $leftParcours = $left->getApcParcours()?->getCode() ?? '';
                        $rightParcours = $right->getApcParcours()?->getCode() ?? '';

                        return [$left->getOrdreLmd(), $leftParcours, $left->getId()] <=> [$right->getOrdreLmd(), $rightParcours, $right->getId()];
                    }
                );

                /** @var Semestre $semestre */
                foreach ($semestres as $semestre) {
                    ++$stats['semestres'];
                    $niveaux = $this->apcNiveauRepository->findBySemestre($semestre);

                    if ($niveaux === []) {
                        $io->text(sprintf(' - %s : aucune compétence trouvée', $this->formatSemestre($semestre)));
                        continue;
                    }

                    $nextOrdre = $this->getNextOrdre($semestre);
                    /** @var ApcNiveau $niveau */
                    foreach ($niveaux as $niveau) {
                        $competence = $niveau->getCompetence();
                        if ($competence === null) {
                            continue;
                        }

                        ++$stats['competences'];
                        if ($this->hasExistingPae($semestre, $competence)) {
                            ++$stats['skipped'];
                            $io->text(sprintf(
                                ' - %s | %s : déjà présent, ignoré',
                                $this->formatSemestre($semestre),
                                $this->formatCompetence($competence),
                            ));
                            continue;
                        }

                        ++$stats['created'];
                        if ($dryRun) {
                            $io->text(sprintf(
                                ' - %s | %s : création simulée',
                                $this->formatSemestre($semestre),
                                $this->formatCompetence($competence),
                            ));
                            ++$nextOrdre;
                            continue;
                        }

                        $this->createPaeForCompetence($semestre, $niveau, $competence, $nextOrdre);
                        $io->text(sprintf(
                            ' - %s | %s : SAÉ PAÉ créée',
                            $this->formatSemestre($semestre),
                            $this->formatCompetence($competence),
                        ));
                        ++$nextOrdre;
                    }
                }
            }

            if ($dryRun === false) {
                $this->entityManager->flush();
                $connection->commit();
            }
        } catch (Throwable $throwable) {
            if ($dryRun === false && $connection->isTransactionActive()) {
                $connection->rollBack();
            }

            $io->error(sprintf('Erreur pendant la génération des SAÉ PAE : %s', $throwable->getMessage()));

            return Command::FAILURE;
        }

        if ($dryRun) {
            $io->note('Exécution en mode dry-run : aucune écriture en base.');
        }

        $io->table(
            ['Versions', 'Semestres', 'Compétences', 'Créées', 'Ignorées'],
            [[
                (string) $stats['versions'],
                (string) $stats['semestres'],
                (string) $stats['competences'],
                (string) $stats['created'],
                (string) $stats['skipped'],
            ]]
        );

        $io->success('Traitement des SAÉ PAE terminé.');

        return Command::SUCCESS;
    }

    private function createPaeForCompetence(Semestre $semestre, ApcNiveau $niveau, ApcCompetence $competence, int $ordre): void
    {
        $sae = new ApcSae();
        $sae->setSemestre($semestre);
        $sae->setOrdre($ordre);
        $sae->setLibelle(self::PAE_LIBELLE);
        $sae->setLibelleCourt('PAÉ ' . ($competence->getNumero() ?? $competence->getId()));
        $sae->setObjectifs(sprintf($this->getPaeObjectifsTemplate($semestre->getOrdreLmd()), $this->formatCompetence($competence)));
        $sae->setDescription(sprintf($this->getPaeDescriptionTemplate($semestre->getOrdreLmd()), $this->formatCompetence($competence)));
        $sae->setExemples(null);
        $sae->setCommentaire($this->buildPaeMarker($competence));
        $sae->setHeuresTotales(0);
        $sae->setCmPpn(0);
        $sae->setTdPpn(0);
        $sae->setTpPpn(0);
        $sae->setProjetPpn(0);
        $sae->setFicheAdaptationLocale(false);
        $sae->setPortfolio(true);
        $sae->setStage(false);

        $this->entityManager->persist($sae);

        $saeCompetence = new ApcSaeCompetence($sae, $competence);
        $this->entityManager->persist($saeCompetence);

        foreach ($niveau->getApcApprentissageCritiques() as $ac) {
            $this->entityManager->persist(new ApcSaeApprentissageCritique($sae, $ac));
        }

        $parcoursLinks = [];
        if ($semestre->getApcParcours() instanceof ApcParcours) {
            $parcoursLink = new ApcSaeParcours($sae, $semestre->getApcParcours());
            $this->entityManager->persist($parcoursLink);
            $parcoursLinks[] = $parcoursLink;
        }

        $sae->setCodeMatiere(Codification::codeSae($sae, $parcoursLinks));
    }

    private function hasExistingPae(Semestre $semestre, ApcCompetence $competence): bool
    {
        $expectedMarker = $this->buildPaeMarker($competence);
        foreach ($semestre->getApcSaes() as $sae) {
            if ($sae->getCommentaire() === $expectedMarker) {
                return true;
            }

            if ($sae->getLibelle() !== self::PAE_LIBELLE) {
                continue;
            }

            foreach ($sae->getApcSaeCompetences() as $saeCompetence) {
                if ($saeCompetence->getCompetence()?->getId() === $competence->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getNextOrdre(Semestre $semestre): int
    {
        $ordre = 0;
        foreach ($semestre->getApcSaes() as $sae) {
            $ordre = max($ordre, $sae->getOrdre() ?? 0);
        }

        return $ordre + 1;
    }

    private function buildPaeMarker(ApcCompetence $competence): string
    {
        return self::PAE_MARKER_PREFIX . ($competence->getNumero() ?? $competence->getId());
    }

    private function formatSemestre(Semestre $semestre): string
    {
        $suffix = $semestre->getApcParcours() instanceof ApcParcours
            ? ' [' . $semestre->getApcParcours()->getCode() . ']'
            : '';

        return sprintf('S%d%s', $semestre->getOrdreLmd(), $suffix);
    }

    private function formatCompetence(ApcCompetence $competence): string
    {
        return trim(($competence->getNomCourt() ?? ('Compétence ' . $competence->getNumero())) . ' (#' . ($competence->getNumero() ?? $competence->getId()) . ')');
    }
}

