#!/bin/sh

set -e

ROOT_DIR="$(dirname "$0")"

echo "Génération des référentiels de compétences"
sh "$ROOT_DIR/genereRefCompetences.sh"

echo "Génération des tableaux"
sh "$ROOT_DIR/genereRefTableaux.sh"

echo "Génération des fichiers LaTeX"
sh "$ROOT_DIR/genereAllLatex.sh"

echo "Fin de la génération complète"
