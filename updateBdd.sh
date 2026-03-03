make drop-db DB=orebut2021
make drop-db DB=orebut2025
make import-db FILE=redigetonbut2021.sql DB=orebut2021
make import-db FILE=export-20260210-201756.sql DB=orebut2025

docker exec -ti -w /var/www/orebut orebut-web bin/console d:s:u -f
docker exec -ti -w /var/www/orebut orebut-web bin/console d:s:u -f --em=import

# Générer un dump SQL complet de orebut2025
docker exec orebut-db mysqldump -u root -pPASSWORD orebut2025 user user_departement iut iut_academie iut_region iut_site iut_site_parcours iut_universite iut_ville qapes_critere qapes_critere_reponse qapes_sae qapes_sae_auteur qapes_sae_critere_reponse qapes_sae_redacteur > /tmp/copy_data.sql

# Vider les tables cibles
docker exec orebut-db mysql -u root -pPASSWORD orebut2021 -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE user; TRUNCATE TABLE user_departement; TRUNCATE TABLE iut; TRUNCATE TABLE iut_academie; TRUNCATE TABLE iut_region; TRUNCATE TABLE iut_site; TRUNCATE TABLE iut_site_parcours; TRUNCATE TABLE iut_universite; TRUNCATE TABLE iut_ville; TRUNCATE TABLE qapes_critere; TRUNCATE TABLE qapes_critere_reponse; TRUNCATE TABLE qapes_sae; TRUNCATE TABLE qapes_sae_auteur; TRUNCATE TABLE qapes_sae_critere_reponse; TRUNCATE TABLE qapes_sae_redacteur; SET FOREIGN_KEY_CHECKS=1;"

# Réimporter depuis le fichier SQL
docker exec -i orebut-db mysql -u root -pPASSWORD orebut2021 < /tmp/copy_data.sql

docker exec -ti -w /var/www/orebut orebut-web bin/console app:create-version
docker exec -ti -w /var/www/orebut orebut-web bin/console app:copy-2027-to-2021

# make reset-passwords
