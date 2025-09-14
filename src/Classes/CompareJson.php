<?php

namespace App\Classes;

class CompareJson
{

    private array $tabActuel;

    private array $tabAncien;

    private array $result;

    public function setTabAncien(array $tabAncien): void
    {
        $this->tabAncien = $tabAncien;
    }

    public function setTabActuel(array $tabActuel): void
    {
        $this->tabActuel = $tabActuel;
    }

    public function compare(): void
    {
        $this->result = $this->compareRecursive($this->tabAncien, $this->tabActuel);
    }

    private function compareRecursive(array $ancien, array $actuel): array
    {
        $result = [];

        // Parcourir les clés de l'ancien tableau
        foreach ($ancien as $key => $value) {
            if (array_key_exists($key, $actuel)) {
                if (is_array($value) && is_array($actuel[$key])) {
                    // Comparer récursivement si les deux valeurs sont des tableaux
                    $result[$key] = $this->compareRecursive($value, $actuel[$key]);
                } elseif ($value === $actuel[$key]) {
                    // Marquer comme identique si les valeurs sont les mêmes
                    $result[$key] = [
                        'typeDiff' => 'identique',
                        'valeur' => $value,
                    ];
                } else {
                    // Marquer comme modifié si les valeurs diffèrent
                    $result[$key] = [
                        'typeDiff' => 'modification',
                        'ancien' => $value,
                        'actuel' => $actuel[$key],
                    ];
                }
            } else {
                // Marquer comme suppression si la clé n'existe plus dans le tableau actuel
                $result[$key] = [
                    'typeDiff' => 'suppression',
                    'ancien' => $value,
                ];
            }
        }

        // Parcourir les clés du tableau actuel pour détecter les ajouts
        foreach ($actuel as $key => $value) {
            if (!array_key_exists($key, $ancien)) {
                $result[$key] = [
                    'typeDiff' => 'ajout',
                    'actuel' => $value,
                ];
            }
        }

        return $result;
    }

    public function getDiff(): array
    {
        return $this->result;
    }
}
