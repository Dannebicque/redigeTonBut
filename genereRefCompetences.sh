#!/bin/sh

VERSION="2027"
SPECIALITES_FILE="$(dirname "$0")/specialites.txt"

echo "Mise  à jour des référentiels de compétences"
while IFS= read -r specialite; do
  [ -z "$specialite" ] && continue
  bin/console app:genere-ref-competence "$specialite" "$VERSION"
done < "$SPECIALITES_FILE"
echo "Fin  de la mise  à jour des référentiels de compétences"
