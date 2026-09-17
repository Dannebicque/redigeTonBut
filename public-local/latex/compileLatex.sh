#!/bin/sh

SPECIALITES_FILE="$(dirname "$0")/../../specialites.txt"

echo "Compilation des fichiers latex en PDF"
while IFS= read -r specialite; do
  [ -z "$specialite" ] && continue
  pdflatex -interaction nonstopmode "PN-BUT-${specialite}.tex"
done < "$SPECIALITES_FILE"
echo "Fin Compilation des fichiers latex en PDF"
