<?php

namespace App\Twig;

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
        ];
    }

//    public function getFunctions(): array
//    {
//        return [
//            new TwigFunction('function_name', [$this, 'doSomething']),
//        ];
//    }

    public function badgeSeuil($value, $seuil)
    {
        if ($value > $seuil) {
            return '<span class="badge bg-danger text-uppercase">' . $value . '</span>';
        }

        return '<span class="badge bg-success text-uppercase">' . $value . '</span>';
    }

    public function badgeEgalite($value, $seuil)
    {
        if ($value !== $seuil) {
            return '<span class="badge bg-danger text-uppercase">' . $value . '</span>';
        }

        return '<span class="badge bg-success text-uppercase">' . $value . '</span>';

    }
}
