# Comparaison des Référentiels de Compétences

## Description

Cette fonctionnalité permet de comparer deux versions de référentiels de compétences côte à côte pour identifier rapidement les changements.

## Routes disponibles

### 1. Comparaison entre deux versions
```
GET /apc/referentiel-competences/comparer/{versionActuelle}/{versionPrecedente}
```

**Paramètres:**
- `versionActuelle` : ID de la version à comparer (version actuelle)
- `versionPrecedente` : ID de la version de référence (version précédente)

**Exemple:**
```
/apc/referentiel-competences/comparer/5/3
```

## Affichages disponibles

### 1. Template `comparer-referentiel.html.twig`
Affichage par défaut avec deux onglets :
- **Synthèse des changements** : Vue d'ensemble avec statistiques par parcours
- **Comparaison détaillée** : Affichage en accordéon pour chaque parcours

### 2. Template `comparer-side-by-side.html.twig`
Affichage côte à côte simplifié :
- Deux colonnes : version actuelle vs version précédente
- Surlignage des nouvelles compétences (vert) et supprimées (rouge)
- Idéal pour les comparaisons rapides

## Indicateurs visuels

### Compétences
- **Badge vert "Nouvelle"** : Compétence présente dans la version actuelle mais pas dans l'ancienne
- **Badge rouge "Supprimée"** : Compétence présente dans l'ancienne version mais pas dans la nouvelle
- **Badge gris "Existante"** : Compétence présente dans les deux versions

### Différences
- **+X (vert)** : Ajout de compétences
- **-X (rouge)** : Suppression de compétences
- **0 (gris)** : Aucun changement

## Comment utiliser

### Via le contrôleur
```php
// Génération d'URL
$url = $this->generateUrl('administration_apc_referentiel_comparer', [
    'versionActuelle' => $versionActuelle->getId(),
    'versionPrecedente' => $versionPrecedente->getId(),
]);
```

### Via un lien Twig
```twig
<a href="{{ path('administration_apc_referentiel_comparer', {
    versionActuelle: versionActuelle.id,
    versionPrecedente: versionPrecedente.id
}) }}" class="btn btn-primary">
    Comparer les versions
</a>
```

## Structure des données

Les templates reçoivent :
- `competencesParcoursActuel` : Compétences par parcours de la version actuelle
- `competencesParcoursPrecedent` : Compétences par parcours de la version précédente
- `versionActuelle` : Entité Version actuelle
- `versionPrecedente` : Entité Version précédente
- `departement` : Entité Département associée

## Personnalisation

Pour personnaliser l'affichage, vous pouvez :
1. Modifier les couleurs dans les styles CSS des templates
2. Ajouter des informations supplémentaires aux compétences
3. Créer des filtres personnalisés pour les parcours
4. Ajouter des détails d'audit (dates, auteurs, etc.)

## Améliorations possibles

- [ ] Ajouter un export PDF de la comparaison
- [ ] Ajouter un historique complet de toutes les versions
- [ ] Créer des graphiques de comparaison
- [ ] Ajouter des commentaires sur les changements
- [ ] Intégrer avec un système de versioning Git

