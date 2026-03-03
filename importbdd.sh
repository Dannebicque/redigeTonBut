
# Vider complètement une base de données
mysql -u 1m918z_orebut -p7n2-eCYzdR3 -e "DROP DATABASE IF EXISTS \`1m918z_orebut\`; CREATE DATABASE \`1m918z_orebut\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Réimporter un fichier SQL
mysql -u root -pPASSWORD orebut2021 < /chemin/vers/redigetonbut2021.sql


#!/bin/sh
set -e
set -u

# importbdd.sh - import local (fichiers .sql uniquement)
# Usage: ./importbdd.sh dump.sql

# --- constantes locales (à adapter si besoin) ---
DB_NAME="redigetonbut2025"
DB_USER="root"
DB_PASS="root"
DB_HOST="127.0.0.1"
DB_PORT="8889"
NEW_HASH='$2y$13$GxjWkZpC7mDl18ICjj.zYOmMaP414olRr65mVw.7tUOW/ZI/a3rue' # remplacer par le hash voulu
# Si nécessaire, forcer le chemin du binaire mysql ici :
# MYSQL_BIN="/Applications/MAMP/Library/bin/mysql"
# -------------------------------------------------

# Détecte le binaire mysql (PATH puis fallback MAMP)
MYSQL_BIN="$(command -v mysql 2>/dev/null || true)"
if [ -z "$MYSQL_BIN" ] && [ -x "/Applications/MAMP/Library/bin/mysql57/bin/mysql" ]; then
  MYSQL_BIN="/Applications/MAMP/Library/bin/mysql57/bin/mysql"
fi

if [ -z "$MYSQL_BIN" ] || [ ! -x "$MYSQL_BIN" ]; then
  echo "mysql introuvable. Ajoutez mysql au PATH ou modifiez la variable MYSQL_BIN dans ce script (ex: /Applications/MAMP/Library/bin/mysql)."
  exit 4
fi

usage() {
  echo "Usage: $0 <dump.sql>"
  exit 1
}

if [ $# -ne 1 ]; then
  usage
fi

DUMP_FILE="$1"

if [ ! -f "$DUMP_FILE" ]; then
  echo "Fichier introuvable : $DUMP_FILE"
  exit 2
fi

case "$DUMP_FILE" in
  *.sql) ;;
  *)
    echo "Le fichier doit être au format .sql"
    exit 3
    ;;
esac

# Exporter le mot de passe pour mysql (évite -p en clair)
export MYSQL_PWD="$DB_PASS"

echo "Suppression et recréation de la base \`$DB_NAME\`..."
"$MYSQL_BIN" -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Import du dump : $DUMP_FILE"
"$MYSQL_BIN" -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" < "$DUMP_FILE"

echo "Mise à jour des mots de passe dans la table \`user\` (colonne \`password\`)..."
ESCAPED_HASH_SQL=$(printf '%s' "$NEW_HASH" | sed "s/'/''/g")
"$MYSQL_BIN" -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" -e "UPDATE \`user\` SET \`password\` = '${ESCAPED_HASH_SQL}';"

# Nettoyage
unset MYSQL_PWD

echo "Import terminé et mots de passe mis à jour."
