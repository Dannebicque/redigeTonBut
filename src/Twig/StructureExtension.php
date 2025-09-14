<?php

namespace App\Twig;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\ApcNiveau;
use App\Entity\ApcSituationProfessionnelle;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StructureExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('badgeSeuil', [$this, 'badgeSeuil'], ['is_safe' => ['html']]),
            new TwigFilter('badgeEgalite', [$this, 'badgeEgalite'], ['is_safe' => ['html']]),
            new TwigFilter('diff_render', [$this, 'diffRender'], ['is_safe' => ['html']]),

        ];
    }

//    public function getFunctions(): array
//    {
//        return [
//            new TwigFunction('function_name', [$this, 'doSomething']),
//        ];
//    }

    public function badgeSeuil(string $value, $seuil): string
    {
        if ($value > $seuil) {
            return '<span class="badge bg-danger text-uppercase">' . $value . '</span>';
        }

        return '<span class="badge bg-success text-uppercase">' . $value . '</span>';
    }

    public function badgeEgalite(string $value, $seuil): string
    {
        if ($value !== $seuil) {
            return '<span class="badge bg-danger text-uppercase">' . $value . '</span>';
        }

        return '<span class="badge bg-success text-uppercase">' . $value . '</span>';

    }

    private function getField(string $field = '') : string
    {
        if ($field === '') {
            return '';
        }
    return '.' . $field;
    }

    public function diffRender(mixed $value, array $diffs, mixed $object, mixed $field = ''): string
    {
        // construire le path à partir de l'objet et du champ

        if ($object instanceof ApcCompetence) {
            $path = 'competences[' . $object->getNumeroIdentifiant() . ']' . $this->getField($field);
        } elseif ($object instanceof ApcComposanteEssentielle) {
            //competences[1].composantes_essentielles[0]
            $path = 'competences[' . $object->getCompetence()?->getNumeroIdentifiant() . '].composantes_essentielles['.$field.']' ;
        } elseif ($object instanceof ApcSituationProfessionnelle) {
            //competences[1].composantes_essentielles[0]
            $path = 'competences[' . $object->getCompetence()?->getNumeroIdentifiant() . '].situations['.$field.']' ;
        } elseif ($object instanceof ApcNiveau) {
            //competences[1].niveaux[0].libelle
            $path = 'competences[' . $object->getCompetence()?->getNumeroIdentifiant() . '].niveaux[' . ($object->getOrdre() - 1) . ']' . $this->getField($field);
        } elseif ($object instanceof ApcApprentissageCritique) {
            //competences[1].niveaux[0].acs[1].libelle
            $niveau = $object->getNiveau();
            $comp = $niveau?->getCompetence();
            $path = 'competences[' . $comp?->getNumeroIdentifiant() . '].niveaux['.($niveau?->getOrdre() - 1).'].acs[' . $object->getOrdre() - 1 . ']' . $this->getField($field);
        } else {
            $path = get_class($object) . '.' . $object->getId() . '.' . $field;
        }



        foreach ($diffs as $diff) {
            if ($diff->path === $path) {
                return match ($diff->type) {
                    'ajout' => "<span class='diff-ajout'>" . htmlspecialchars((string)$diff->newValue) . "</span>",
                    'suppression' => "<del class='diff-suppression'>" . htmlspecialchars((string)$diff->oldValue) . "</del>",
                    'modification' => "<del class='diff-modification'>" . htmlspecialchars((string)$diff->oldValue) . "</del> → <span class='diff-ajout'>" . htmlspecialchars((string)$diff->newValue) . "</span>",
                    'ordre' => "<span class='diff-ordre'>(ordre changé)</span>",
                    default => htmlspecialchars((string)$diff->newValue),
                };
            }
        }

        // Pas de différence : afficher normalement
        return htmlspecialchars((string)$value);
    }
}
