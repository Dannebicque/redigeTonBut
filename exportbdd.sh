#!/bin/sh
set -e

# Répertoire du script (racine du projet si placé à la racine)
DIR="$(cd "$(dirname "$0")" && pwd)"
DATE="$(date +%Y%m%d-%H%M%S)"
OUT="$DIR/export-$DATE.sql"

# Valeurs par défaut (modifiable) ou fournis en argument: ./exportbdd.sh my_db
DB_NAME="${1:-${DB_NAME:-}}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

# Si un .env existe à la racine, extraire les variables courantes (Laravel/ Symfony)
if [ -f "$DIR/.env.local" ]; then
  echo "Lecture des paramètres de connexion dans .env.local"
  get_env() {
    grep -E "^$1=" "$DIR/.env.local" 2>/dev/null | head -n1 | cut -d= -f2- | tr -d '"'
  }
  echo $(get_env DB_DATABASE)
  DB_NAME="$(get_env DB_DATABASE)"
  DB_USER="$(get_env DB_USERNAME)"
  DB_PASS="$(get_env DB_PASSWORD)"
  DB_HOST="$(get_env DB_HOST)"
  DB_PORT="$(get_env DB_PORT)"
fi

if [ -z "$DB_NAME" ]; then
  echo "Usage: $0 <database_name>  (ou définir DB_NAME/DB_DATABASE dans .env ou variables d'environnement)"
  exit 1
fi

# Exporter le mot de passe de façon temporaire pour mysqldump
export MYSQL_PWD="$DB_PASS"

# Exécution de l'export
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" > "$OUT"

# Nettoyage
unset MYSQL_PWD

echo "Export terminé : $OUT"
