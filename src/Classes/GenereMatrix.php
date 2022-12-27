<?php

namespace App\Classes;

use App\Classes\Apc\ApcStructure;
use App\Entity\Departement;
use Knp\Snappy\Pdf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class GenereMatrix
{

    private string $dir;
    private array $tParcours;
    private array $competencesParcours;
    private Departement $departement;
    private $competences;
    private Filesystem $filesystem;

    public function __construct(
        KernelInterface $kernel,
        private ApcStructure $apcStructure,
        private Environment $twig

    ) {
        $this->dir = $kernel->getProjectDir() . '/public/matrix/';
        $this->filesystem = new Filesystem();

    }

    public function genereSpecialite(Departement $departement)
    {
        $this->departement = $departement;
        $this->getDataReferentiel();


        foreach ($this->departement->getApcParcours() as $parcours) {
            $name = $this->departement->getSigle() . '-' . $parcours->getCode() . '.matrix';
            //pour chaque parcours
            $tab = [];
            $tab['institution'] = 'all';
            $tab['name'] = $this->departement->getSigle() . ' - ' . $parcours->getLibelle();
            $tab['description'] = $this->departement->getLibelle() . ', parcours : ' . $parcours->getLibelle();
            $tab['selfassess'] = false;
            $tab['evidencestatuses'] = [
                'begun' => '',
                'incomplete' => '',
                'partialcomplete' => '',
                'completed' => '',
            ];

            $standards = [];
            $substandards = [];
            foreach ($this->competencesParcours[$parcours->getId()] as $competence) {
                //-> page compétence
                $standards[] = [ //peut être les parcours ??
                    'shortname' => $competence->getNomcourt(),
                    'name' => $competence->getLibelle(),
                    'description' => $competence->getLibelle(),
                    'standardid' => $competence->getId()
                ];

                /*
                 * {% for i in 1..3 %}
        {% if parcoursNiveaux[parcours.id][competence.id][i] is defined %}
        {
            "shortname":"Niveau {{ parcoursNiveaux[parcours.id][competence.id][i].ordre }}",
            "name":"{{ parcoursNiveaux[parcours.id][competence.id][i].ordre }}. {{ parcoursNiveaux[parcours.id][competence.id][i].libelle }}",
            "description":"{{ parcoursNiveaux[parcours.id][competence.id][i].libelle }}",
            "standardid":{{ competence.id }},
            "elementid": "{{ parcoursNiveaux[parcours.id][competence.id][i].id }}"
        },
            {% for ac in parcoursNiveaux[parcours.id][competence.id][i].apcApprentissageCritiques %}
            {
                "shortname":"{{ ac.code }}",
                "name":"{{ ac.libelle }}",
                "description":"{{ ac.libelle }}",
                "standardid":{{ competence.id }},
                "parentelementid": "{{ parcoursNiveaux[parcours.id][competence.id][i].id }}",
                "elementid":"{{ ac.id }}"
            }
            {% endfor %}
        {% endif %}{%
                 */

                for ($i = 1; $i <= 3; $i++) {
                    if (array_key_exists($i, $this->tParcours[$parcours->getId()][$competence->getId()])) {
                        $substandards[] = [
                            'shortname' => $this->tParcours[$parcours->getId()][$competence->getId()][$i]->getOrdre(),
                            'name' => $this->tParcours[$parcours->getId()][$competence->getId()][$i]->getOrdre() . ' - ' . $this->tParcours[$parcours->getId()][$competence->getId()][$i]->getLibelle(),
                            'description' => $this->tParcours[$parcours->getId()][$competence->getId()][$i]->getLibelle(),
                            'standardid' => $competence->getId(),
                            'elementid' => $this->tParcours[$parcours->getId()][$competence->getId()][$i]->getId(),
                        ];

                        foreach ($this->tParcours[$parcours->getId()][$competence->getId()][$i]->getApcApprentissageCritiques() as $ac) {
                            $substandards[] = [
                                'shortname' => $ac->getCode(),
                                'name' => $ac->getLibelle(),
                                'description' => $ac->getLibelle(),
                                'standardid' => $competence->getId(),
                                'elementid' => $ac->getId(),
                                'parentelementid' => $this->tParcours[$parcours->getId()][$competence->getId()][$i]->getId()
                            ];
                        }
                    }
                }
            }
            $tab['standards'] = $standards;
            $tab['standardelements'] = $substandards;
            $json['framework'] = $tab;
            file_put_contents($this->dir . $name, json_encode($json, JSON_PRETTY_PRINT));
        }
    }

    private function getDataReferentiel()
    {
        $this->tParcours = $this->apcStructure->parcoursNiveaux($this->departement);
        $this->competences = $this->departement->getApcCompetences();
        $tComp = [];
        foreach ($this->competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }
        $this->competencesParcours = [];

        foreach ($this->tParcours as $key => $parc) {
            $this->competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $this->competencesParcours[$key][] = $tComp[$k];
            }
        }
    }
}
