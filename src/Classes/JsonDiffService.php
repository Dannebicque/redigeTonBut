<?php
namespace App\Classes;

use App\DTO\DiffItemDto;

class JsonDiffService
{
    private array $objectKeys = [
        'competences' => 'numero_identifiant',
        'competences.niveaux' => 'ordre',
        'competences.niveaux.acs' => 'code',
        'parcours' => 'code',
        'parcours.annees' => 'ordre',
        'parcours.annees.competences' => 'id',
    ];

    public function compare(array $old, array $new): array
    {
        return $this->compareRecursive($old, $new);
    }

    private function compareRecursive(mixed $old, mixed $new, string $path = ''): array
    {
        $diffs = [];

        if (is_array($old) && is_array($new)) {
            if ($this->isList($old) && $this->isList($new)) {
                // C’est un tableau ordonné
                $diffs = array_merge($diffs, $this->compareList($old, $new, $path));
            } elseif ($this->isAssociativeArray($old) || $this->isAssociativeArray($new)) {
                // C’est un tableau associatif (objet en JSON)
                $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

                foreach ($allKeys as $key) {
                    $currentPath = $path === '' ? (string)$key : $path . '.' . $key;
                    $existsInOld = array_key_exists($key, $old);
                    $existsInNew = array_key_exists($key, $new);

                    if ($existsInOld && $existsInNew) {
                        $diffs = array_merge($diffs, $this->compareRecursive($old[$key], $new[$key], $currentPath));
                    } elseif ($existsInOld && !$existsInNew) {
                        $diffs[] = new DiffItemDto($currentPath, $old[$key], null, 'suppression');
                    } elseif (!$existsInOld && $existsInNew) {
                        $diffs[] = new DiffItemDto($currentPath, null, $new[$key], 'ajout');
                    }
                }
            } else {
                // Valeurs simples comparées directement
                if ($old !== $new) {
                    $diffs[] = new DiffItemDto($path, $old, $new, 'modification');
                }
            }
        } else {
            // Valeurs simples comparées directement
            if ($old !== $new) {
                $diffs[] = new DiffItemDto($path, $old, $new, 'modification');
            }
        }

        return $diffs;
    }

    private function compareList(array $old, array $new, string $path): array
    {
        $diffs = [];

        $key = $this->getObjectKey($path);

        if ($key !== null) {
            // C’est une liste d’objets identifiables
            $oldMap = $this->mapByKey($old, $key);
            $newMap = $this->mapByKey($new, $key);

            $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

            foreach ($allKeys as $id) {
                $existsInOld = array_key_exists($id, $oldMap);
                $existsInNew = array_key_exists($id, $newMap);
                $currentPath = $path . '[' . $id . ']';

                if ($existsInOld && $existsInNew) {
                    $diffs = array_merge($diffs, $this->compareRecursive($oldMap[$id], $newMap[$id], $currentPath));
                } elseif ($existsInOld && !$existsInNew) {
                    $diffs[] = new DiffItemDto($currentPath, $oldMap[$id], null, 'suppression');
                } elseif (!$existsInOld && $existsInNew) {
                    $diffs[] = new DiffItemDto($currentPath, null, $newMap[$id], 'ajout');
                }
            }

            // Comparaison de l’ordre (ordre significatif !)
            $oldOrder = array_values(array_keys($oldMap));
            $newOrder = array_values(array_keys($newMap));

            if ($oldOrder !== $newOrder) {
                $diffs[] = new DiffItemDto($path, $oldOrder, $newOrder, 'ordre');
            }

        } else {
            // C’est une liste "simple"
            $max = max(count($old), count($new));
            for ($i = 0; $i < $max; $i++) {
                $currentPath = $path . '[' . $i . ']';
                $existsInOld = array_key_exists($i, $old);
                $existsInNew = array_key_exists($i, $new);

                if ($existsInOld && $existsInNew) {
                    $diffs = array_merge($diffs, $this->compareRecursive($old[$i], $new[$i], $currentPath));
                } elseif ($existsInOld && !$existsInNew) {
                    $diffs[] = new DiffItemDto($currentPath, $old[$i], null, 'suppression');
                } elseif (!$existsInOld && $existsInNew) {
                    $diffs[] = new DiffItemDto($currentPath, null, $new[$i], 'ajout');
                }
            }

            if (array_values($old) !== array_values($new)) {
                $diffs[] = new DiffItemDto($path, array_values($old), array_values($new), 'ordre');
            }
        }

        return $diffs;
    }

    private function isList(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    private function isAssociativeArray(array $array): bool
    {
        return !$this->isList($array);
    }

    private function getObjectKey(string $path): ?string
    {
        return $this->objectKeys[$path] ?? null;
    }

    private function mapByKey(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item) && array_key_exists($key, $item)) {
                $result[(string) $item[$key]] = $item;
            }
        }
        return $result;
    }
}

