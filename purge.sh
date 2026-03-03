#!/bin/bash

DB_NAME="1m918z_orebut"
DB_USER="1m918z_orebut"
DB_PASS="7n2-eCYzdR3"
DB_HOST="1m918z.myd.infomaniak.com"
DUMP_FILE="$1"

if [ ! -f "$DUMP_FILE" ]; then
  echo "Fichier introuvable : $DUMP_FILE"
  exit 1
fi

# Désactiver les vérifications de clés étrangères
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS=0;"

# Supprimer toutes les tables
echo "Suppression des tables..."
TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | tail -n +2)
for table in $TABLES; do
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DROP TABLE IF EXISTS \`$table\`;"
done

# Réactiver les vérifications
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS=1;"

# Importer le nouveau dump
echo "Import du dump : $DUMP_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DUMP_FILE"
