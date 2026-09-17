#!/bin/sh

SPECIALITES_FILE="$(dirname "$0")/specialites.txt"

echo "Mise  à jour des tableaux"
while IFS= read -r specialite; do
  [ -z "$specialite" ] && continue
  bin/console app:genere-tableaux "$specialite"
done < "$SPECIALITES_FILE"
echo "Fin  de la mise  à jour des tableaux"
